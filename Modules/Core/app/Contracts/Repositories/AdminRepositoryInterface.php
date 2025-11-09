<?php

namespace Modules\Core\Contracts\Repositories;

use Modules\Core\Models\Admin;

interface AdminRepositoryInterface
{
    /**
     * Find Admin by email
     */
    public function findByEmail(string $email): ?Admin;

    /**
     * Create new Admin
     */
    public function create(array $data): Admin;

    /**
     * Update Admin last login timestamp
     */
    public function updateLastLogin(Admin $admin): void;

    /**
     * Find Admin by ID
     */
    public function findById(int $id): ?Admin;

    /**
     * Update Admin data
     */
    public function update(Admin $admin, array $data): Admin;
}