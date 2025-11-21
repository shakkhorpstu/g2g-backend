<?php

namespace Modules\Core\Contracts\Repositories;

use Modules\Core\Models\ServiceCategory;

interface ServiceCategoryRepositoryInterface
{
    public function paginate(int $perPage = 15): mixed;
    public function all(): mixed;
    public function find(int $id): ?ServiceCategory;
    public function create(array $data): ServiceCategory;
    public function update(ServiceCategory $category, array $data): ServiceCategory;
    public function delete(ServiceCategory $category): bool;
}