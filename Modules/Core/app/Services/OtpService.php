<?php

namespace Modules\Core\Services;

use App\Shared\Services\BaseService;
use Modules\Core\Contracts\Repositories\OtpRepositoryInterface;
use Modules\Core\Events\OtpSent;
use Modules\Core\Events\OtpVerified;
use Illuminate\Support\Facades\Crypt;

class OtpService extends BaseService
{
    protected OtpRepositoryInterface $otpRepository;

    public function __construct(OtpRepositoryInterface $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }

    /**
     * Resend OTP
     */
    public function resendOtp(string $identifier, string $type, string $otpableType, int $otpableId): array
    {
        return $this->executeWithTransaction(function () use ($identifier, $type, $otpableType, $otpableId) {
            // Find existing pending OTP
            $existingOtp = $this->otpRepository->findByOtpableAndType($otpableType, $otpableId, $type);
            
            if ($existingOtp && !$existingOtp->isExpired()) {
                $this->fail('OTP already sent. Please wait before requesting a new one.', 429);
            }

            // Generate new OTP
            $otpCode = $this->generateOtpCode();
            
            $otpVerification = $this->otpRepository->create([
                'otpable_type' => $otpableType,
                'otpable_id' => $otpableId,
                'identifier' => $identifier,
                'otp_code' => Crypt::encrypt($otpCode),
                'type' => $type,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0,
                'max_attempts' => 3
            ]);

            // Dispatch event to send OTP
            event(new OtpSent($otpVerification));

            return $this->success([
                'otp_id' => $otpVerification->id,
                'expires_at' => $otpVerification->expires_at
            ], 'OTP sent successfully');
        });
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $identifier, string $otpCode, string $type): array
    {
        return $this->executeWithTransaction(function () use ($identifier, $otpCode, $type) {
            $otpVerification = $this->otpRepository->findByIdentifierAndType($identifier, $type);
            
            if (!$otpVerification) {
                $this->fail('Invalid or expired OTP', 400);
            }

            if ($otpVerification->isExpired()) {
                $this->fail('OTP has expired', 400);
            }

            if ($otpVerification->hasMaxAttemptsReached()) {
                $this->fail('Maximum OTP verification attempts reached', 429);
            }

            // Decrypt and verify OTP
            $decryptedOtp = Crypt::decrypt($otpVerification->otp_code);
            
            if ($decryptedOtp !== $otpCode) {
                $otpVerification->incrementAttempts();
                $this->fail('Invalid OTP code', 400);
            }

            // Mark as verified
            $otpVerification->markAsVerified();

            // Dispatch event
            event(new OtpVerified($otpVerification));

            return $this->success([
                'verified_at' => $otpVerification->verified_at,
                'otpable_type' => $otpVerification->otpable_type,
                'otpable_id' => $otpVerification->otpable_id
            ], 'OTP verified successfully');
        });
    }

    /**
     * Generate OTP code
     */
    private function generateOtpCode(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}