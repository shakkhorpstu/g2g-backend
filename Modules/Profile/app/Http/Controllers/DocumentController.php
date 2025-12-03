<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Profile\Http\Requests\StoreOrUpdateDocumentRequest;
use Modules\Profile\Services\DocumentService;

class DocumentController extends ApiController
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        parent::__construct();
        $this->documentService = $documentService;
    }

    /**
     * Get all active document types
     *
     * @return JsonResponse
     */
    public function getTypes(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->documentService->getDocumentTypes()
        );
    }

    /**
     * Store or update a document
     *
     * @param StoreOrUpdateDocumentRequest $request
     * @return JsonResponse
     */
    public function storeOrUpdate(StoreOrUpdateDocumentRequest $request): JsonResponse
    {
        $documentTypeId = $request->input('document_type_id');
        $frontFile = $request->file('front_file');
        $backFile = $request->file('back_file');
        $metadata = $request->input('metadata');

        return $this->executeServiceForCreation(
            fn() => $this->documentService->storeOrUpdateDocument(
                $documentTypeId,
                $frontFile,
                $backFile,
                $metadata
            )
        );
    }

    /**
     * Get all documents for authenticated user with optional status filter
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status'); // pending, verified, rejected, uploaded

        // Validate status if provided
        if ($status && !in_array($status, ['pending', 'verified', 'rejected', 'uploaded'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status. Allowed values: pending, verified, rejected, uploaded',
            ], 422);
        }

        return $this->executeService(
            fn() => $this->documentService->getAllDocuments($status)
        );
    }

    /**
     * Get proof of ID document for regular users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userDocument(Request $request): JsonResponse
    {
        $status = $request->query('status');

        // Validate status if provided
        if ($status && !in_array($status, ['pending', 'verified', 'rejected', 'uploaded'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status. Allowed values: pending, verified, rejected, uploaded',
            ], 422);
        }

        return $this->executeService(
            fn() => $this->documentService->getAllDocuments($status, 'proof_of_id')
        );
    }

    /**
     * Get single document by document type ID with files
     *
     * @param int $documentTypeId
     * @return JsonResponse
     */
    public function show(int $documentTypeId): JsonResponse
    {
        return $this->executeService(
            fn() => $this->documentService->getDocumentByType($documentTypeId)
        );
    }
}
