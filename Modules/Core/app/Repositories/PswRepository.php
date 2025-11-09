<?php

namespace Modules\Core\Repositories;

use Modules\Core\Models\Psw;
use Modules\Core\Contracts\Repositories\PswRepositoryInterface;
use Illuminate\Support\Facades\Hash;

/**
 * PSW Repository
 * 
 * Handles data access operations for PSW model
 */
class PswRepository implements PswRepositoryInterface
{
    /**
     * Find PSW by email
     */
    public function findByEmail(string $email): ?Psw
    {
        return Psw::where('email', $email)->first();
    }



    /**
     * Create new PSW
     */
    public function create(array $data): Psw
    {
        // Hash password if present
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return Psw::create($data);
    }

    /**
     * Update PSW last login timestamp
     */
    public function updateLastLogin(Psw $psw): void
    {
        $psw->update(['last_login_at' => now()]);
    }

    /**
     * Update PSW password
     *
     * @param Psw $psw
     * @param string $password
     * @return Psw
     */
    public function updatePassword(Psw $psw, string $password): Psw
    {
        $psw->update([
            'password' => Hash::make($password)
        ]);

        return $psw->fresh();
    }

    /**
     * Find PSW by ID
     */
    public function findById(int $id): ?Psw
    {
        return Psw::find($id);
    }

    /**
     * Update PSW data
     */
    public function update(Psw $psw, array $data): Psw
    {
        // Hash password if present
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $psw->update($data);
        return $psw->fresh();
    }

    /**
     * Get active PSWs
     */
    public function getActivePsws(int $limit = 10): array
    {
        return Psw::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get available PSWs
     */
    public function getAvailablePsws(int $limit = 10): array
    {
        return Psw::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}