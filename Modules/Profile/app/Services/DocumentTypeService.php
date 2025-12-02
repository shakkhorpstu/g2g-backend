<?php

namespace Modules\Profile\Services;

use App\Shared\Services\BaseService;
use Modules\Profile\Contracts\Repositories\DocumentTypeRepositoryInterface;

class DocumentTypeService extends BaseService
{
    protected DocumentTypeRepositoryInterface $documentTypeRepository;

    public function __construct(DocumentTypeRepositoryInterface $documentTypeRepository)
    {
        $this->documentTypeRepository = $documentTypeRepository;
    }

    /**
     * Get all document types
     *
     * @param bool $activeOnly
     * @return array
     */
    public function index(bool $activeOnly = false): array
    {
        $documentTypes = $this->documentTypeRepository->getAll($activeOnly);

        return $this->success(
            $documentTypes,
            'Document types retrieved successfully.'
        );
    }

    /**
     * Get single document type
     *
     * @param int $id
     * @return array
     */
    public function show(int $id): array
    {
        $documentType = $this->documentTypeRepository->find($id);

        if (!$documentType) {
            $this->fail('Document type not found.', 404);
        }

        return $this->success(
            $documentType,
            'Document type retrieved successfully.'
        );
    }

    /**
     * Create new document type
     *
     * @param array $data
     * @return array
     */
    public function store(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Set default values
            $data['active'] = $data['active'] ?? true;
            $data['is_required'] = $data['is_required'] ?? false;
            $data['both_sided'] = $data['both_sided'] ?? false;
            $data['both_sided_required'] = $data['both_sided_required'] ?? false;
            $data['sort_order'] = $data['sort_order'] ?? 0;
            
            // Set default allowed mime types if not provided
            if (!isset($data['allowed_mime']) || empty($data['allowed_mime'])) {
                $data['allowed_mime'] = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            }
            
            // Set default max size if not provided (5MB)
            $data['max_size_kb'] = $data['max_size_kb'] ?? 5120;

            $documentType = $this->documentTypeRepository->create($data);

            return $this->success(
                $documentType,
                'Document type created successfully.',
                201
            );
        });
    }

    /**
     * Update document type
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        return $this->executeWithTransaction(function () use ($id, $data) {
            $documentType = $this->documentTypeRepository->find($id);

            if (!$documentType) {
                $this->fail('Document type not found.', 404);
            }

            $updatedDocumentType = $this->documentTypeRepository->update($id, $data);

            return $this->success(
                $updatedDocumentType,
                'Document type updated successfully.'
            );
        });
    }

    /**
     * Delete document type
     *
     * @param int $id
     * @return array
     */
    public function destroy(int $id): array
    {
        return $this->executeWithTransaction(function () use ($id) {
            $documentType = $this->documentTypeRepository->find($id);

            if (!$documentType) {
                $this->fail('Document type not found.', 404);
            }

            $this->documentTypeRepository->delete($id);

            return $this->success(
                null,
                'Document type deleted successfully.'
            );
        });
    }
}
