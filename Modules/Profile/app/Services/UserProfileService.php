<?php

namespace Modules\Profile\Services;

use Modules\Profile\Contracts\Repositories\UserProfileRepositoryInterface;
use Modules\Core\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class UserProfileService extends BaseService
{
    /**
     * User profile repository instance
     *
     * @var UserProfileRepositoryInterface
     */
    protected UserProfileRepositoryInterface $userProfileRepository;

    /**
     * UserProfileService constructor
     *
     * @param UserProfileRepositoryInterface $userProfileRepository
     */
    public function __construct(UserProfileRepositoryInterface $userProfileRepository)
    {
        $this->userProfileRepository = $userProfileRepository;
    }

    /**
     * Get user profile
     *
     * @return array
     */
    public function getProfile(): array
    {
        $user = Auth::guard('api')->user();
        
        if (!$user) {
            $this->fail('User not authenticated', 401);
        }

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
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                $this->fail('User not authenticated', 401);
            }

            // Update profile data
            $profileData = array_intersect_key($data, array_flip(['language_id']));
            
            if (!empty($profileData)) {
                $this->userProfileRepository->updateOrCreate($user->id, $profileData);
            }

            // Get updated user with profile
            $userWithProfile = $this->userProfileRepository->getUserWithProfile($user->id);

            return $this->success([
                'user' => $userWithProfile,
                'profile' => $userWithProfile->profile
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
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                $this->fail('User not authenticated', 401);
            }

            $deleted = $this->userProfileRepository->delete($user->id);

            if (!$deleted) {
                $this->fail('Profile not found', 404);
            }

            return $this->success(null, 'User profile deleted successfully');
        });
    }
}