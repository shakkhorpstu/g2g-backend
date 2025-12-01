<?php

namespace Modules\Core\Services;

use App\Shared\Services\BaseService;
use Modules\Core\Models\OtpVerification;
use Modules\Core\Events\OtpSent;
use Modules\Core\Contracts\Repositories\UserRepositoryInterface;
use Modules\Core\Contracts\Repositories\PswRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class RegistrationOtpService extends BaseService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected PswRepositoryInterface $pswRepository
    ) {
    }

    /**
     * Generate and send OTP for client registration/verification
     */
    public function sendOtpForClient(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $userId = $data['user_id'] ?? null;
            $phone = $data['phone'] ?? null;

            if (!$userId) {
                $this->fail('user_id is required', 422);
            }

            $user = $this->userRepository->findById((int) $userId);
            if (!$user) {
                $this->fail('User not found', 404);
            }

            // Update phone on user record
            $this->userRepository->update($user, ['phone_number' => $phone]);

            $email = $user->email ?? null;

            return $this->generateAndSendOtp($user, get_class($user), $phone, $email);
        });
    }

    /**
     * Generate and send OTP for PSW registration/verification
     */
    public function sendOtpForPsw(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $pswId = $data['user_id'] ?? null;
            $phone = $data['phone'] ?? null;

            if (!$pswId) {
                $this->fail('user_id is required', 422);
            }

            $psw = $this->pswRepository->findById((int) $pswId);
            if (!$psw) {
                $this->fail('PSW not found', 404);
            }

            // Update phone on PSW record
            $this->pswRepository->update($psw, ['phone_number' => $phone]);

            $email = $psw->email ?? null;

            return $this->generateAndSendOtp($psw, get_class($psw), $phone, $email);
        });
    }

    /**
     * Verify OTP for client
     */
    public function verifyOtpForClient(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $userId = $data['user_id'] ?? null;
            $otpCode = $data['otp_code'] ?? null;

            if (!$userId || !$otpCode) {
                $this->fail('user_id and otp_code are required', 422);
            }

            $user = $this->userRepository->findById((int) $userId);
            if (!$user) {
                $this->fail('User not found', 404);
            }

            return $this->verifyOtp($user->id, get_class($user), $otpCode);
        });
    }

    /**
     * Verify OTP for PSW
     */
    public function verifyOtpForPsw(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $pswId = $data['user_id'] ?? null;
            $otpCode = $data['otp_code'] ?? null;

            if (!$pswId || !$otpCode) {
                $this->fail('user_id and otp_code are required', 422);
            }

            $psw = $this->pswRepository->findById((int) $pswId);
            if (!$psw) {
                $this->fail('PSW not found', 404);
            }

            return $this->verifyOtp($psw->id, get_class($psw), $otpCode);
        });
    }

    /**
     * Core logic: generate OTP code and send to phone/email
     */
    protected function generateAndSendOtp($user, string $userType, ?string $phone, ?string $email): array
    {
        if (!$phone) {
            $this->fail('Phone number is required', 422);
        }

        // Use phone as primary identifier
        $identifier = $phone;

        // Generate 6-digit OTP
        $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Invalidate any previous pending OTPs for this user
        OtpVerification::where('otpable_id', $user->id)
            ->where('otpable_type', $userType)
            ->where('type', 'account_verification')
            ->where('status', 'pending')
            ->delete();

        // Create new OTP record in otp_verifications table
        $otp = OtpVerification::create([
            'otpable_id' => $user->id,
            'otpable_type' => $userType,
            'identifier' => $identifier,
            'otp_code' => Crypt::encrypt($otpCode),
            'type' => 'account_verification',
            'status' => 'pending',
            'expires_at' => now()->addMinutes(config('app.reset_otp_life', 10)),
            'attempts' => 0,
            'max_attempts' => 3,
        ]);

        // Dispatch event so queued listener handles mail/SMS consistently with existing flow
        event(new OtpSent($otp));

        Log::info('Registration OTP dispatched', [
            'otp_id' => $otp->id,
            'identifier' => $identifier,
            'user_id' => $user->id,
            'type' => 'account_verification'
        ]);

        return $this->success([
            'otp_id' => $otp->id,
            'sent_to' => [
                'phone' => $this->maskPhone($phone),
                'email' => $email ? $this->maskEmail($email) : null,
            ],
            'expires_in_minutes' => config('app.reset_otp_life', 10),
        ], 'OTP sent to phone number' . ($email ? ' and email' : ''), 201);
    }

    /**
     * Core logic: verify OTP code
     */
    protected function verifyOtp(int $userId, string $userType, string $otpCode): array
    {
        $otp = OtpVerification::where('otpable_id', $userId)
            ->where('otpable_type', $userType)
            ->where('type', 'account_verification')
            ->where('status', 'pending')
            ->first();

        if (!$otp) {
            $this->fail('Invalid or expired OTP', 422);
        }

        if ($otp->isExpired()) {
            $otp->update(['status' => 'expired']);
            $this->fail('OTP code has expired', 422);
        }

        if ($otp->hasMaxAttemptsReached()) {
            $otp->update(['status' => 'failed']);
            $this->fail('Maximum OTP verification attempts reached', 429);
        }

        // Decrypt and verify OTP
        try {
            $decryptedOtp = Crypt::decrypt($otp->otp_code);
        } catch (\Exception $e) {
            $this->fail('Invalid OTP format', 422);
        }

        if ($decryptedOtp !== $otpCode) {
            $otp->incrementAttempts();
            $this->fail('Invalid OTP code', 422);
        }

        // Mark OTP as verified
        $otp->markAsVerified();

        // Mark user email as verified based on user type
        if ($userType === 'Modules\Core\Models\User' || $userType === 'App\Models\User') {
            $user = $this->userRepository->findById($userId);
            if ($user) {
                $this->userRepository->markEmailAsVerified($user);
            }
        } elseif ($userType === 'Modules\Core\Models\Psw') {
            $psw = $this->pswRepository->findById($userId);
            if ($psw && method_exists($this->pswRepository, 'markEmailAsVerified')) {
                $this->pswRepository->markEmailAsVerified($psw);
            }
        }

        return $this->success([
            'verified' => true,
            'verified_at' => now(),
        ], 'Account verified successfully');
    }

    /**
     * Mask email for privacy
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));

        return $maskedName . '@' . $domain;
    }

    /**
     * Mask phone for privacy
     */
    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length < 4) {
            return $phone;
        }

        return substr($phone, 0, 2) . str_repeat('*', $length - 4) . substr($phone, -2);
    }
}
