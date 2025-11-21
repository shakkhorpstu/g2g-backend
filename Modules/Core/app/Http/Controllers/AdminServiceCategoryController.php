<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Core\Services\ServiceCategoryService;
use Modules\Core\Http\Requests\ServiceCategoryStoreRequest;
use Modules\Core\Http\Requests\ServiceCategoryUpdateRequest;

class AdminServiceCategoryController extends ApiController
{
    protected ServiceCategoryService $serviceCategoryService;

    public function __construct(ServiceCategoryService $serviceCategoryService)
    {
        parent::__construct();
        $this->serviceCategoryService = $serviceCategoryService;
    }

    public function index(): JsonResponse
    {
        return $this->executeService(fn() => $this->serviceCategoryService->paginate());
    }

    public function store(ServiceCategoryStoreRequest $request): JsonResponse
    {
        return $this->executeServiceForCreation(fn() => $this->serviceCategoryService->create($request->validated()));
    }

    public function show(int $id): JsonResponse
    {
        return $this->executeService(fn() => $this->serviceCategoryService->show($id));
    }

    public function update(ServiceCategoryUpdateRequest $request, int $id): JsonResponse
    {
        return $this->executeService(fn() => $this->serviceCategoryService->update($id, $request->validated()));
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->executeService(fn() => $this->serviceCategoryService->delete($id));
    }
}