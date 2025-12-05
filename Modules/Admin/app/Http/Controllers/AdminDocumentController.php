<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\AdminDocumentService;

class AdminDocumentController extends ApiController
{
    protected AdminDocumentService $adminDocumentService;

    public function __construct(AdminDocumentService $adminDocumentService)
    {
        parent::__construct();
        $this->adminDocumentService = $adminDocumentService;
    }

    /**
     * Get all documents for a specific client
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function clientDocuments(Request $request, int $userId): JsonResponse
    {
        $status = $request->query('status');

        return $this->executeService(
            fn() => $this->adminDocumentService->getClientDocuments($userId, $status)
        );
    }

    /**
     * Get all documents for a specific PSW
     *
     * @param Request $request
     * @param int $pswId
     * @return JsonResponse
     */
    public function pswDocuments(Request $request, int $pswId): JsonResponse
    {
        $status = $request->query('status');

        return $this->executeService(
            fn() => $this->adminDocumentService->getPswDocuments($pswId, $status)
        );
    }
}
