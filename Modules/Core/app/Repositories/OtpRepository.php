<?php

namespace Modules\Core\Repositories;

use Modules\Core\Contracts\Repositories\OtpRepositoryInterface;
use Modules\Core\Models\OtpVerification;

class OtpRepository implements OtpRepositoryInterface
{
    public function create(array $data): OtpVerification
    {
        return OtpVerification::create($data);
    }
    
    public function findByOtpableAndType(string $otpableType, int $otpableId, string $type): ?OtpVerification
    {
        return OtpVerification::where('otpable_type', $otpableType)
            ->where('otpable_id', $otpableId)
            ->where('type', $type)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }
    
    public function findByIdentifierAndType(string $identifier, string $type): ?OtpVerification
    {
        return OtpVerification::where('identifier', $identifier)
            ->where('type', $type)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }
    
    public function findValidOtp(string $identifier, string $otpCode, string $type): ?OtpVerification
    {
        return OtpVerification::where('identifier', $identifier)
            ->where('otp_code', $otpCode)
            ->where('type', $type)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }
    
    public function deleteExpiredOtps(): int
    {
        return OtpVerification::where('expires_at', '<', now())
            ->orWhere('status', 'verified')
            ->delete();
    }
    
    public function update(OtpVerification $otpVerification, array $data): bool
    {
        return $otpVerification->update($data);
    }

    public function findByOtpableAndTypeVerified(string $otpableType, int $otpableId, string $type): ?OtpVerification
   {
    return OtpVerification::where('otpable_type', $otpableType)
        ->where('otpable_id', $otpableId)
        ->where('type', $type)
        ->where('status', 'verified')
        ->where('expires_at', '>', now())
        ->first();
   }
}