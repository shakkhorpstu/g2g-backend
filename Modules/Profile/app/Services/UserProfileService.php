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
     * Get user with profile by user ID (for admin use)
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserWithProfileById(int $userId): ?array
    {
        return $this->userProfileRepository->getUserWithProfile($userId);
    }
}