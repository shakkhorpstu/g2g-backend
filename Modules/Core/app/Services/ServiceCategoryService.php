<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\Repositories\ServiceCategoryRepositoryInterface;
use App\Shared\Services\BaseService;
use App\Shared\Exceptions\ServiceException;
use Modules\Core\Models\ServiceCategory;

class ServiceCategoryService extends BaseService
{
    protected ServiceCategoryRepositoryInterface $repository;

    public function __construct(ServiceCategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    // Admin listing
    public function listAll(): array
    {
        $categories = $this->repository->all();
        return $this->success($categories, 'Service categories retrieved successfully');
    }

    public function show(int $id): array
    {
        $category = $this->repository->find($id);
        if (!$category) {
            $this->fail('Service category not found', 404);
        }
        return $this->success($category, 'Service category retrieved');
    }

    public function create(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $category = $this->repository->create($data);
            return $this->success($category, 'Service category created successfully', 201);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->executeWithTransaction(function () use ($id, $data) {
            $category = $this->repository->find($id);
            if (!$category) {
                $this->fail('Service category not found', 404);
            }
            $updated = $this->repository->update($category, $data);
            return $this->success($updated, 'Service category updated successfully');
        });
    }

    public function delete(int $id): array
    {
        return $this->executeWithTransaction(function () use ($id) {
            $category = $this->repository->find($id);
            if (!$category) {
                $this->fail('Service category not found', 404);
            }
            $this->repository->delete($category);
            return $this->success(null, 'Service category deleted successfully');
        });
    }
}