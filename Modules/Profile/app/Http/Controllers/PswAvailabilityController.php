<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Profile\Http\Requests\SyncPswAvailabilityRequest;
use Modules\Profile\Services\PswAvailabilityService;

class PswAvailabilityController extends ApiController
{
    protected PswAvailabilityService $service;

    public function __construct(PswAvailabilityService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->executeService(fn() => $this->service->listForAuthenticatedPsw(), 'Availability retrieved');
    }

    public function sync(SyncPswAvailabilityRequest $request): JsonResponse
    {
        $data = $request->getSanitized();

        return $this->executeService(fn() => $this->service->syncForAuthenticatedPsw($data), 'Availability synced');
    }
}
