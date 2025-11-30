<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Core\Services\ResourceService;
use Illuminate\Http\JsonResponse;

class ResourceController extends ApiController
{
    protected ResourceService $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        parent::__construct();
        $this->resourceService = $resourceService;
    }

    /**
     * GET /v1/languages
     */
    public function languages(): JsonResponse
    {
        return $this->executeService(fn() => $this->resourceService->getLanguages());
    }
}
