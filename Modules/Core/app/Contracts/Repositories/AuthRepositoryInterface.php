<?php

namespace Modules\Core\Contracts\Repositories;

use Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Auth Repository Interface
 * 
 * Defines methods for authentication-related database operations
 */
interface AuthRepositoryInterface
{
    /**
     * Create a new user
     *
     * @param array $data User data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Update user data
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User;

    /**
     * Update user password
     *
     * @param User $user
     * @param string $password
     * @return User
     */
    public function updatePassword(User $user, string $password): User;

    /**
     * Update last login timestamp
     *
     * @param User $user
     * @return User
     */
    public function updateLastLogin(User $user): User;

    /**
     * Get all users
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Get users by role
     *
     * @param string $role
     * @return Collection
     */
    public function getByRole(string $role): Collection;

    /**
     * Check if email exists
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool;

    /**
     * Delete user
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool;

    /**
     * Get user with relations
     *
     * @param int $id
     * @param array $relations
     * @return User|null
     */
    public function findWithRelations(int $id, array $relations = []): ?User;

    /**
     * Search users by criteria
     *
     * @param array $criteria
     * @return Collection
     */
    public function searchByCriteria(array $criteria): Collection;
}