<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Profile\Http\Requests\SyncPswServiceCategoriesRequest;
use Modules\Profile\Services\PswServiceCategoryService;

class PswServiceCategoryController extends ApiController
{
    protected PswServiceCategoryService $service;

    public function __construct(PswServiceCategoryService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->executeService(fn() => $this->service->listForAuthenticatedPsw(), 'Service categories retrieved');
    }

    public function sync(SyncPswServiceCategoriesRequest $request): JsonResponse
    {
        $data = $request->getSanitized();

        return $this->executeService(fn() => $this->service->syncForAuthenticatedPsw($data), 'Service categories synced');
    }
}
