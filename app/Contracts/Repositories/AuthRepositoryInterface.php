<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface AuthRepositoryInterface
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user
     */
    public function createUser(array $data): User;

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $email, string $password): bool;

    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool;

    /**
     * Update user last login time
     */
    public function updateLastLogin(int $userId): bool;
}