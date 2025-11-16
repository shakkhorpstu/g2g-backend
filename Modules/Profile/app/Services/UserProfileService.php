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

        return $this->success([
            'user' => $userWithProfile,
            'profile' => $userWithProfile->profile
        ], 'User profile retrieved successfully');
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

            // Update profile data
            // $profileData = array_intersect_key($data, array_flip(['language_id']));
            $profileData = $data;

            // If email change requested and differs from current, send account verification OTP first
            if (isset($profileData['email']) && $profileData['email'] !== $user->email) {
                $this->otpService->resendOtp(
                    $user->email, // send to existing email for verification
                    'account_verification',
                    get_class($user),
                    $user->id
                );
                // Optionally, you may want to store the new email in a 'pending_email' field instead of updating immediately.
            }
            
            if(!empty($profileData)) {
                $this->userProfileRepository->updateOrCreate($user->id, $profileData);
            }

            // Get updated user with profile
            $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

            return $this->success([
                'user' => $userWithProfile,
            ], 'User profile updated successfully');
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
}