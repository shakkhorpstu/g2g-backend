<?php

namespace Modules\Auth\Contracts\Repositories;

use App\Shared\Contracts\Repositories\BaseRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * User Repository Interface
 * 
 * Defines contracts for user-related database operations within the Auth module.
 * This interface is specific to authentication and user management functionality.
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by email or fail
     *
     * @param string $email
     * @return User
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByEmailOrFail(string $email): User;

    /**
     * Create user with hashed password
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User;

    /**
     * Update user password
     *
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function updatePassword(int $userId, string $password): bool;

    /**
     * Verify user credentials
     *
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function verifyCredentials(string $email, string $password): ?User;

    /**
     * Check if email exists
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool;

    /**
     * Get active users
     *
     * @param array $columns
     * @return Collection
     */
    public function getActiveUsers(array $columns = ['*']): Collection;

    /**
     * Get users by role
     *
     * @param string $role
     * @param array $columns
     * @return Collection
     */
    public function getUsersByRole(string $role, array $columns = ['*']): Collection;

    /**
     * Search users by name or email
     *
     * @param string $searchTerm
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchUsers(string $searchTerm, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get recent users (last N days)
     *
     * @param int $days
     * @return Collection
     */
    public function getRecentUsers(int $days = 30): Collection;

    /**
     * Update user last login timestamp
     *
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin(int $userId): bool;

    /**
     * Get users with specific permissions (if you implement permissions)
     *
     * @param array $permissions
     * @return Collection
     */
    public function getUsersWithPermissions(array $permissions): Collection;

    /**
     * Soft delete user account
     *
     * @param int $userId
     * @return bool
     */
    public function deactivateUser(int $userId): bool;

    /**
     * Restore soft deleted user account
     *
     * @param int $userId
     * @return bool
     */
    public function reactivateUser(int $userId): bool;
}