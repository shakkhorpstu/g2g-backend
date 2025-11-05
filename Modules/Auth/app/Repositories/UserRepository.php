<?php

namespace Modules\Auth\Repositories;

use App\Shared\Repositories\BaseRepository;
use Modules\Auth\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * User Repository Implementation
 * 
 * Handles all user-related database operations for the Auth module.
 * Implements both base repository functionality and auth-specific methods.
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Get the model instance
     */
    protected function getModel(): Model
    {
        return new User();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findFirstBy(['email' => $email]);
    }

    /**
     * Find user by email or fail
     */
    public function findByEmailOrFail(string $email): User
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found with email: ' . $email);
        }
        
        return $user;
    }

    /**
     * Create user with hashed password
     */
    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->create($data);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $password): bool
    {
        return $this->updateById($userId, [
            'password' => Hash::make($password)
        ]);
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $email, string $password): ?User
    {
        $user = $this->findByEmail($email);
        
        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }
        
        return null;
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool
    {
        return $this->exists(['email' => $email]);
    }

    /**
     * Get active users
     */
    public function getActiveUsers(array $columns = ['*']): Collection
    {
        return $this->findBy(['status' => 'active'], $columns);
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role, array $columns = ['*']): Collection
    {
        return $this->findBy(['role' => $role], $columns);
    }

    /**
     * Search users by name or email
     */
    public function searchUsers(string $searchTerm, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $this->model->newQuery()
                ->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('email', 'like', '%' . $searchTerm . '%')
                ->paginate($perPage);
                
        } catch (\Throwable $exception) {
            Log::error('Repository SearchUsers Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'search_term' => $searchTerm,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Get recent users (last N days)
     */
    public function getRecentUsers(int $days = 30): Collection
    {
        try {
            return $this->model->newQuery()
                ->where('created_at', '>=', now()->subDays($days))
                ->orderBy('created_at', 'desc')
                ->get();
                
        } catch (\Throwable $exception) {
            Log::error('Repository GetRecentUsers Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'days' => $days,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Update user last login timestamp
     */
    public function updateLastLogin(int $userId): bool
    {
        try {
            return $this->updateById($userId, [
                'last_login_at' => now()
            ]);
        } catch (\Throwable $exception) {
            Log::error('Repository UpdateLastLogin Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'user_id' => $userId,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Get users with specific permissions
     */
    public function getUsersWithPermissions(array $permissions): Collection
    {
        try {
            // This is a placeholder - implement based on your permission system
            // Example with a permissions relationship:
            return $this->model->newQuery()
                ->whereHas('permissions', function ($query) use ($permissions) {
                    $query->whereIn('name', $permissions);
                })
                ->get();
                
        } catch (\Throwable $exception) {
            Log::error('Repository GetUsersWithPermissions Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'permissions' => $permissions,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Soft delete user account (deactivate)
     */
    public function deactivateUser(int $userId): bool
    {
        try {
            return $this->updateById($userId, [
                'status' => 'inactive',
                'deactivated_at' => now()
            ]);
        } catch (\Throwable $exception) {
            Log::error('Repository DeactivateUser Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'user_id' => $userId,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }

    /**
     * Restore soft deleted user account (reactivate)
     */
    public function reactivateUser(int $userId): bool
    {
        try {
            return $this->updateById($userId, [
                'status' => 'active',
                'deactivated_at' => null
            ]);
        } catch (\Throwable $exception) {
            Log::error('Repository ReactivateUser Error: ' . $exception->getMessage(), [
                'repository' => static::class,
                'user_id' => $userId,
                'exception' => $exception,
            ]);
            
            throw $exception;
        }
    }
}