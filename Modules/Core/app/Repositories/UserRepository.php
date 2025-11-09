<?php

namespace Modules\Core\Repositories;

use Modules\Core\Contracts\Repositories\UserRepositoryInterface;
use Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * User Repository
 * 
 * Implementation of user-specific database operations
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user
     *
     * @param array $data User data
     * @return User
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Update user data
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    /**
     * Update user password
     *
     * @param User $user
     * @param string $password
     * @return User
     */
    public function updatePassword(User $user, string $password): User
    {
        $user->update([
            'password' => Hash::make($password)
        ]);

        return $user->fresh();
    }

    /**
     * Update last login timestamp
     *
     * @param User $user
     * @return User
     */
    public function updateLastLogin(User $user): User
    {
        $user->update([
            'last_login_at' => now()
        ]);

        return $user->fresh();
    }

    /**
     * Delete user
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = User::query();

        // Apply filters if provided
        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get all users
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return User::all();
    }

    /**
     * Count all users
     *
     * @return int
     */
    public function count(): int
    {
        return User::count();
    }

    /**
     * Get users by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection
    {
        return User::where('status', $status)->get();
    }

    /**
     * Verify user email
     *
     * @param User $user
     * @return User
     */
    public function markEmailAsVerified(User $user): User
    {
        $user->update([
            'is_verified' => true,
            'email_verified_at' => now()
        ]);

        return $user->fresh();
    }

    /**
     * Update user status
     *
     * @param User $user
     * @param string $status
     * @return User
     */
    public function updateStatus(User $user, string $status): User
    {
        $user->update([
            'status' => $status
        ]);

        return $user->fresh();
    }
}