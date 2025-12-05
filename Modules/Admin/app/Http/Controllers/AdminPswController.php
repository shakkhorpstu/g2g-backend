<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\AdminPswService;

class AdminPswController extends ApiController
{
    protected AdminPswService $adminPswService;

    public function __construct(AdminPswService $adminPswService)
    {
        parent::__construct();
        $this->adminPswService = $adminPswService;
    }

    /**
     * Get paginated list of PSWs
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
        ];

        return $this->executeService(
            fn() => $this->adminPswService->index($perPage, array_filter($filters))
        );
    }

    /**
     * Get single PSW by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->adminPswService->show($id)
        );
    }
}
