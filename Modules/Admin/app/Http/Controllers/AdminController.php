<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Admin\Http\Requests\StoreAdminRequest;
use Modules\Admin\Http\Requests\UpdateAdminRequest;
use Illuminate\Http\Request;
use Modules\Admin\Services\AdminService;

class AdminController extends ApiController
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        parent::__construct();
        $this->adminService = $adminService;
    }

    /**
     * Get paginated list of admins
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $search = $request->query('search');

        return $this->executeService(
            fn() => $this->adminService->index($perPage, $search)
        );
    }

    /**
     * Create new admin
     *
     * @param StoreAdminRequest $request
     * @return JsonResponse
     */
    public function store(StoreAdminRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->adminService->store($request->validated())
        );
    }

    /**
     * Get single admin by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->adminService->show($id)
        );
    }

    /**
     * Update admin
     *
     * @param UpdateAdminRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateAdminRequest $request, int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->adminService->update($id, $request->validated())
        );
    }

    /**
     * Delete admin
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->adminService->destroy($id)
        );
    }
}
