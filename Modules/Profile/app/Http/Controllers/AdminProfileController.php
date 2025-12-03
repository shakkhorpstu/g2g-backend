<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Profile\Services\AdminProfileService;

class AdminProfileController extends ApiController
{
    protected AdminProfileService $adminProfileService;

    public function __construct(AdminProfileService $adminProfileService)
    {
        parent::__construct();
        $this->adminProfileService = $adminProfileService;
    }

    /**
     * Get admin profile with profile picture
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->adminProfileService->getProfile()
        );
    }
}
