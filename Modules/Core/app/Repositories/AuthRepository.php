<?php

namespace Modules\Core\Repositories;

use Modules\Core\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Auth Repository
 * 
 * Implementation of authentication-related database operations
 */
class AuthRepository implements AuthRepositoryInterface
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
     * Get all users
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return User::all();
    }

    /**
     * Get users by role
     *
     * @param string $role
     * @return Collection
     */
    public function getByRole(string $role): Collection
    {
        return User::where('role', $role)->get();
    }

    /**
     * Check if email exists
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = User::where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
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
     * Get user with relations
     *
     * @param int $id
     * @param array $relations
     * @return User|null
     */
    public function findWithRelations(int $id, array $relations = []): ?User
    {
        return User::with($relations)->find($id);
    }

    /**
     * Search users by criteria
     *
     * @param array $criteria
     * @return Collection
     */
    public function searchByCriteria(array $criteria): Collection
    {
        $query = User::query();

        if (isset($criteria['name'])) {
            $query->where('name', 'like', '%' . $criteria['name'] . '%');
        }

        if (isset($criteria['email'])) {
            $query->where('email', 'like', '%' . $criteria['email'] . '%');
        }

        if (isset($criteria['role'])) {
            $query->where('role', $criteria['role']);
        }

        if (isset($criteria['phone'])) {
            $query->where('phone', 'like', '%' . $criteria['phone'] . '%');
        }

        return $query->get();
    }
}