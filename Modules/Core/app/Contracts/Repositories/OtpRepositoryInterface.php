<?php

namespace Modules\Core\Contracts\Repositories;

use Modules\Core\Models\OtpVerification;

interface OtpRepositoryInterface
{
    public function create(array $data): OtpVerification;
    
    public function findByOtpableAndType(string $otpableType, int $otpableId, string $type): ?OtpVerification;
    
    public function findByIdentifierAndType(string $identifier, string $type): ?OtpVerification;
    
    public function findValidOtp(string $identifier, string $otpCode, string $type): ?OtpVerification;
    
    public function deleteExpiredOtps(): int;
    
    public function update(OtpVerification $otpVerification, array $data): bool;
}