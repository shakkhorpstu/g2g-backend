<?php

namespace Modules\Profile\Services;

use Modules\Profile\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Shared\Services\BaseService;
use Modules\Core\Services\OtpService;

class UserProfileService extends BaseService
{
    /**
     * User profile repository instance
     *
     * @var UserProfileRepositoryInterface
     */
    protected UserProfileRepositoryInterface $userProfileRepository;
    protected OtpService $otpService;

    /**
     * UserProfileService constructor
     *
     * @param UserProfileRepositoryInterface $userProfileRepository
     */
    public function __construct(UserProfileRepositoryInterface $userProfileRepository, OtpService $otpService)
    {
        $this->userProfileRepository = $userProfileRepository;
        $this->otpService = $otpService;
    }

    /**
     * Get user profile
     *
     * @return array
     */
    public function getProfile(): array
    {
        $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

        $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

        return $this->success($userWithProfile, 'User profile retrieved successfully');
    }

    /**
     * Update user profile
     *
     * @param array $data
     * @return array
     */
    public function updateProfile(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

            $pendingChanges = [];

            // Email change request
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $this->otpService->resendOtp(
                    $data['email'],
                    'email_update',
                    get_class($user),
                    $user->id
                );
                $pendingChanges[] = ['type' => 'email', 'value' => $data['email']];
                unset($data['email']);
            }

            // Phone number change request
            if (isset($data['phone_number']) && $data['phone_number'] !== $user->phone_number) {
                $this->otpService->resendOtp(
                    $data['phone_number'],
                    'phone_update',
                    get_class($user),
                    $user->id
                );
                $pendingChanges[] = ['type' => 'phone', 'value' => $data['phone_number']];
                unset($data['phone_number']);
            }

            // If any pending changes require verification, return early
            if (!empty($pendingChanges)) {
                return $this->success([
                    'pending_verifications' => $pendingChanges
                ], 'Verification OTP sent for pending contact changes. Please verify to complete.');
            }

            // Update remaining profile data
            if (!empty($data)) {
                $this->userProfileRepository->updateOrCreate($user->id, $data);
            }

            $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

            return $this->success($userWithProfile, 'User profile updated successfully');
        });
    }

    /**
     * Verify email change with OTP and complete the update
     *
     * @param array $data
     * @return array
     */
    public function verifyContactChange(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

            $type = $data['type']; // email or phone
            $newValue = $data['new_value'];

            $otpType = $type === 'email' ? 'email_update' : 'phone_update';

            // Verify OTP
            $this->otpService->verifyOtp(
                $newValue,
                $data['otp_code'],
                $otpType
            );

            // Update user field accordingly
            if ($type === 'email') {
                $user->email = $newValue;
            } else {
                $user->phone_number = $newValue;
            }

            // Append meta log entry
            $meta = $user->meta ?? [];
            if (!is_array($meta)) { $meta = []; }
            $meta[] = [
                'action_type' => $type === 'email' ? 'Email updated' : 'Phone number updated',
                'value' => $newValue,
                'timestamp' => now()->toISOString()
            ];
            $user->meta = $meta;
            $user->save();

            $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

            return $this->success($userWithProfile, ucfirst($type) . ' updated successfully');
        });
    }

    /**
     * Create initial profile for user
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function createInitialProfile(int $userId, array $data = []): array
    {
        return $this->executeWithTransaction(function () use ($userId, $data) {
            $profileData = array_merge(['language_id' => null], $data);
            
            $profile = $this->userProfileRepository->create($userId, $profileData);

            return $this->success($profile, 'User profile created successfully');
        });
    }

    /**
     * Delete user profile
     *
     * @return array
     */
    public function deleteProfile(): array
    {
        return $this->executeWithTransaction(function () {
            $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

            $deleted = $this->userProfileRepository->delete($user->id);

            if (!$deleted) {
                $this->fail('Profile not found', 404);
            }

            return $this->success(null, 'User profile deleted successfully');
        });
    }

    /**
     * Send 2FA OTP to user's chosen contact (email or phone)
     *
     * @param array $data ['method' => 'email'|'phone']
     * @return array
     */
    public function sendTwoFactor(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

            $method = $data['method'] ?? null;
            if (!in_array($method, ['email', 'phone'])) {
                $this->fail('Invalid method. Must be "email" or "phone"', 422);
            }

            $identifier = $method === 'email' ? $user->email : $user->phone_number;
            if (!$identifier) {
                $this->fail('No contact information available for selected method', 422);
            }

            // Send OTP for two_factor
            $result = $this->otpService->resendOtp(
                $identifier,
                'two_factor',
                get_class($user),
                $user->id
            );

            return $this->success($result['data'] ?? [], 'Two-factor OTP sent');
        });
    }

    /**
     * Verify 2FA OTP and enable two factor on profile
     *
     * @param array $data ['method' => 'email'|'phone', 'otp_code' => '123456']
     * @return array
     */
    public function verifyTwoFactor(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = $this->getAuthenticatedUserOrFail(['api'], 'User not authenticated');

            $method = $data['method'] ?? null;
            $otpCode = $data['otp_code'] ?? null;

            if (!in_array($method, ['email', 'phone'])) {
                $this->fail('Invalid method. Must be "email" or "phone"', 422);
            }

            if (!$otpCode) {
                $this->fail('OTP code is required', 422);
            }

            $identifier = $method === 'email' ? $user->email : $user->phone_number;
            if (!$identifier) {
                $this->fail('No contact information available for selected method', 422);
            }

            // Verify OTP
            $this->otpService->verifyOtp($identifier, $otpCode, 'two_factor');

            // Update user profile 2FA flags
            $this->userProfileRepository->update($user->id, [
                '2fa_enabled' => true,
                '2fa_identifier_key' => $method,
            ]);

            $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

            return $this->success($userWithProfile, 'Two-factor authentication enabled');
        });
    }
}