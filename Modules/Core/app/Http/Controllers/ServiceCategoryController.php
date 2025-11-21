<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Core\Services\ServiceCategoryService;

class ServiceCategoryController extends ApiController
{
    protected ServiceCategoryService $serviceCategoryService;

    public function __construct(ServiceCategoryService $serviceCategoryService)
    {
        parent::__construct();
        $this->serviceCategoryService = $serviceCategoryService;
    }

    // List for clients / psw
    public function list(): JsonResponse
    {
        return $this->executeService(fn() => $this->serviceCategoryService->listAll());
    }

    public function show(int $id): JsonResponse
    {
        return $this->executeService(fn() => $this->serviceCategoryService->show($id));
    }
}