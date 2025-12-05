<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\AdminClientService;

class AdminClientController extends ApiController
{
    protected AdminClientService $adminClientService;

    public function __construct(AdminClientService $adminClientService)
    {
        parent::__construct();
        $this->adminClientService = $adminClientService;
    }

    /**
     * Get paginated list of clients
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'email' => $request->query('email'),
        ];

        return $this->executeService(
            fn() => $this->adminClientService->index($perPage, array_filter($filters))
        );
    }

    /**
     * Get single client by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->adminClientService->show($id)
        );
    }
}
