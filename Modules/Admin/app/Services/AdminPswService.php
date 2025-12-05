<?php

namespace Modules\Admin\Services;

use App\Shared\Services\BaseService;
use Modules\Core\Services\PswService;
use Modules\Profile\Services\PswProfileService;

class AdminPswService extends BaseService
{
    protected PswService $pswService;
    protected PswProfileService $pswProfileService;

    public function __construct(
        PswService $pswService,
        PswProfileService $pswProfileService
    ) {
        $this->pswService = $pswService;
        $this->pswProfileService = $pswProfileService;
    }

    /**
     * Get paginated list of PSWs with their profiles
     *
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function index(int $perPage = 15, array $filters = []): array
    {
        // Use Core module service for PSW listing with filters
        $paginator = $this->pswService->paginate($perPage, $filters);

        // Use Profile module service to get complete PSW data with profile
        $psws = collect($paginator->items())->map(function ($psw) {
            return $this->pswProfileService->getPswWithProfileById($psw->id);
        })->filter()->values();

        return $this->success(
            [
                'data' => $psws,
                'meta' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
            'PSWs retrieved successfully.'
        );
    }

    /**
     * Get single PSW with profile
     *
     * @param int $id
     * @return array
     */
    public function show(int $id): array
    {
        // Check if PSW exists via Core service
        $psw = $this->pswService->findById($id);

        if (!$psw) {
            $this->fail('PSW not found.', 404);
        }

        // Get complete PSW data via Profile service
        $pswData = $this->pswProfileService->getPswWithProfileById($id);

        if (!$pswData) {
            $this->fail('PSW profile not found.', 404);
        }

        return $this->success(
            $pswData,
            'PSW retrieved successfully.'
        );
    }
}
