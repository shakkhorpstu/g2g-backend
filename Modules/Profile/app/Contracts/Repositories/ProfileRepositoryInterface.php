<?php

namespace Modules\Profile\Contracts\Repositories;

use Modules\Core\Models\User;

interface ProfileRepositoryInterface
{
    /**
     * Find user by ID
     */
    public function findById(int $id);

    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $data): User;

    /**
     * Delete user profile
     */
    public function deleteProfile(int $userId): bool;

    /**
     * Create user profile (admin only)
     */
    public function createProfile(array $data): User;

    /**
     * Get user profile with relationships
     */
    public function getProfileWithRelations(int $userId, array $relations = []): ?User;
}