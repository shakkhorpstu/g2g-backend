<?php

namespace Modules\Core\Contracts\Repositories;

use Modules\Core\Models\Psw;

interface PswRepositoryInterface
{
    /**
     * Find PSW by email
     */
    public function findByEmail(string $email): ?Psw;



    /**
     * Create new PSW
     */
    public function create(array $data): Psw;

    /**
     * Update PSW last login timestamp
     */
    public function updateLastLogin(Psw $psw): void;

    /**
     * Update PSW password
     */
    public function updatePassword(Psw $psw, string $password): Psw;

    /**
     * Find PSW by ID
     */
    public function findById(int $id): ?Psw;

    /**
     * Update PSW data
     */
    public function update(Psw $psw, array $data): Psw;

    /**
     * Get active PSWs
     */
    public function getActivePsws(int $limit = 10): array;

    /**
     * Get available PSWs
     */
    public function getAvailablePsws(int $limit = 10): array;
}