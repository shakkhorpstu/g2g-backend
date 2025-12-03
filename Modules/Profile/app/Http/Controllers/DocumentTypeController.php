<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Profile\Http\Requests\StoreDocumentTypeRequest;
use Modules\Profile\Http\Requests\UpdateDocumentTypeRequest;
use Modules\Profile\Services\DocumentTypeService;

class DocumentTypeController extends ApiController
{
    protected DocumentTypeService $documentTypeService;

    public function __construct(DocumentTypeService $documentTypeService)
    {
        parent::__construct();
        $this->documentTypeService = $documentTypeService;
    }

    /**
     * Get all document types
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->documentTypeService->index(false)
        );
    }

    /**
     * Get single document type
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->documentTypeService->show($id)
        );
    }

    /**
     * Create new document type
     *
     * @param StoreDocumentTypeRequest $request
     * @return JsonResponse
     */
    public function store(StoreDocumentTypeRequest $request): JsonResponse
    {
        return $this->executeServiceForCreation(
            fn() => $this->documentTypeService->store($request->validated())
        );
    }

    /**
     * Update document type
     *
     * @param int $id
     * @param UpdateDocumentTypeRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateDocumentTypeRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->documentTypeService->update($id, $request->validated())
        );
    }

    /**
     * Delete document type
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->documentTypeService->destroy($id)
        );
    }
}
