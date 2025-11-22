<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Payment\Http\Requests\StoreClientCardRequest;
use Modules\Payment\Http\Requests\UpdateClientCardRequest;
use Modules\Payment\Services\ClientCardService;

class ClientCardController extends ApiController
{
    protected ClientCardService $service;

    public function __construct(ClientCardService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->executeService(fn() => $this->service->index());
    }

    public function store(StoreClientCardRequest $request): JsonResponse
    {
        return $this->executeServiceForCreation(fn() => $this->service->store($request->validated()));
    }

    public function show(string $id): JsonResponse
    {
        return $this->executeService(fn() => $this->service->show($id));
    }

    public function update(UpdateClientCardRequest $request, string $id): JsonResponse
    {
        return $this->executeService(fn() => $this->service->update($id, $request->validated()));
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->executeService(fn() => $this->service->destroy($id));
    }
}
