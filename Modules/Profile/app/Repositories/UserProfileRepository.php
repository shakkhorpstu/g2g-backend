<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Contracts\Repositories\UserProfileRepositoryInterface;
use Modules\Core\Models\UserProfile;
use Modules\Core\Models\User;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    /**
     * Find user profile by user ID
     *
     * @param int $userId
     * @return UserProfile|null
     */
    public function findByUserId(int $userId): ?UserProfile
    {
        return UserProfile::where('user_id', $userId)->first();
    }

    /**
     * Create user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
    public function create(int $userId, array $data): UserProfile
    {
        $data['user_id'] = $userId;
        return UserProfile::create($data);
    }

    /**
     * Update user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
    public function update(int $userId, array $data): UserProfile
    {
        $profile = $this->findByUserId($userId);
        
        if (!$profile) {
            throw new \Exception('User profile not found');
        }

        $profile->update($data);
        return $profile->fresh();
    }

    /**
     * Update or create user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
     public function updateOrCreate(int $userId, array $data): User
    {
        return User::updateOrCreate(
            ['id' => $userId],
            $data
        );
    }

    /**
     * Delete user profile
     *
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId): bool
    {
        $profile = $this->findByUserId($userId);
        
        if (!$profile) {
            return false;
        }

        return $profile->delete();
    }

    /**
     * Get user with profile
     *
     * @param int $userId
     * @return User|null
     */
    public function getUserWithProfile(int $userId): ?User
    {
        return User::with('profile')->find($userId);
    }
}