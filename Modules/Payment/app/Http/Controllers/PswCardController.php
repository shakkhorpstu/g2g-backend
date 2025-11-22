<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Payment\Http\Requests\StorePswCardRequest;
use Modules\Payment\Http\Requests\UpdatePswCardRequest;
use Modules\Payment\Services\PswCardService;

class PswCardController extends ApiController
{
    protected PswCardService $service;

    public function __construct(PswCardService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->executeService(fn() => $this->service->index());
    }

    public function store(StorePswCardRequest $request): JsonResponse
    {
        return $this->executeServiceForCreation(fn() => $this->service->store($request->validated()));
    }

    public function show(string $id): JsonResponse
    {
        return $this->executeService(fn() => $this->service->show($id));
    }

    public function update(UpdatePswCardRequest $request, string $id): JsonResponse
    {
        return $this->executeService(fn() => $this->service->update($id, $request->validated()));
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->executeService(fn() => $this->service->destroy($id));
    }
}
