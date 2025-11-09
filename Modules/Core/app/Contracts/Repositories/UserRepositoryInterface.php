<?php

namespace Modules\Core\Contracts\Repositories;

use Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * User Repository Interface
 * 
 * Defines methods for user-specific database operations
 */
interface UserRepositoryInterface
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
     * Delete user
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool;

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Get all users
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Count all users
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get users by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Verify user email
     *
     * @param User $user
     * @return User
     */
    public function markEmailAsVerified(User $user): User;

    /**
     * Update user status
     *
     * @param User $user
     * @param string $status
     * @return User
     */
    public function updateStatus(User $user, string $status): User;
}