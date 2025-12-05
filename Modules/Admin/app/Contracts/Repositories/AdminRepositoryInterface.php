<?php

namespace Modules\Admin\Contracts\Repositories;

interface AdminRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): array;

    public function find(int $id): ?array;

    public function findByEmail(string $email): ?array;

    public function create(array $data): array;

    public function update(int $id, array $data): array;

    public function delete(int $id): bool;
}
