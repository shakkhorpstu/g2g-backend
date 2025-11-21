<?php

namespace Modules\Core\Repositories;

use Modules\Core\Contracts\Repositories\ServiceCategoryRepositoryInterface;
use Modules\Core\Models\ServiceCategory;

class ServiceCategoryRepository implements ServiceCategoryRepositoryInterface
{
    public function paginate(int $perPage = 15): mixed
    {
        return ServiceCategory::orderByDesc('id')->paginate($perPage);
    }

    public function all(): mixed
    {
        return ServiceCategory::orderByDesc('id')->get();
    }

    public function find(int $id): ?ServiceCategory
    {
        return ServiceCategory::find($id);
    }

    public function create(array $data): ServiceCategory
    {
        return ServiceCategory::create($data);
    }

    public function update(ServiceCategory $category, array $data): ServiceCategory
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(ServiceCategory $category): bool
    {
        return (bool) $category->delete();
    }
}