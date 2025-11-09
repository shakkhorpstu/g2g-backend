<?php

namespace Modules\Core\Repositories;

use Modules\Core\Models\Admin;
use Modules\Core\Contracts\Repositories\AdminRepositoryInterface;
use Illuminate\Support\Facades\Hash;

/**
 * Admin Repository
 * 
 * Handles data access operations for Admin model
 */
class AdminRepository implements AdminRepositoryInterface
{
    /**
     * Find Admin by email
     */
    public function findByEmail(string $email): ?Admin
    {
        return Admin::where('email', $email)->first();
    }

    /**
     * Create new Admin
     */
    public function create(array $data): Admin
    {
        // Hash password if present
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return Admin::create($data);
    }

    /**
     * Update Admin last login timestamp
     */
    public function updateLastLogin(Admin $admin): void
    {
        $admin->update(['last_login_at' => now()]);
    }

    /**
     * Update Admin password
     *
     * @param Admin $admin
     * @param string $password
     * @return Admin
     */
    public function updatePassword(Admin $admin, string $password): Admin
    {
        $admin->update([
            'password' => Hash::make($password)
        ]);

        return $admin->fresh();
    }

    /**
     * Find Admin by ID
     */
    public function findById(int $id): ?Admin
    {
        return Admin::find($id);
    }

    /**
     * Update Admin data
     */
    public function update(Admin $admin, array $data): Admin
    {
        // Hash password if present
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $admin->update($data);
        return $admin->fresh();
    }
}