<?php

namespace Modules\Profile\Contracts\Repositories;

use Modules\Profile\Models\UserProfile;
use Modules\Core\Models\User;

interface UserProfileRepositoryInterface
{
    /**
     * Find user profile by user ID
     *
     * @param int $userId
     * @return UserProfile|null
     */
    public function findByUserId(int $userId): ?UserProfile;

    /**
     * Create user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
    public function create(int $userId, array $data): UserProfile;

    /**
     * Update user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
    public function update(int $userId, array $data): UserProfile;

    /**
     * Update or create user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
    public function updateOrCreate(int $userId, array $data): User;

    /**
     * Delete user profile
     *
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId): bool;

    /**
     * Get user with profile and profile picture
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserWithProfile(int $userId): ?array;
}