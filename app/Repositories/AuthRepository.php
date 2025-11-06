<?php

namespace App\Repositories;

use App\Models\User;
use App\Shared\Repositories\BaseRepository;
use App\Contracts\Repositories\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthRepository extends BaseRepository implements AuthRepositoryInterface
{
    /**
     * Get the model instance
     */
    protected function getModel(): User
    {
        return new User();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        return $this->query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'user',
        ]);
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $email, string $password): bool
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }

        return Hash::check($password, $user->password);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool
    {
        return $this->query()->where('email', $email)->exists();
    }

    /**
     * Update user last login time
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->query()
            ->where('id', $userId)
            ->update(['last_login_at' => now()]);
    }
}