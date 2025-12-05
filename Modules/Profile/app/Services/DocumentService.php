<?php

namespace Modules\Profile\Services;

use App\Shared\Services\BaseService;
use App\Shared\Services\FileStorageService;
use Modules\Profile\Contracts\Repositories\DocumentTypeRepositoryInterface;
use Modules\Profile\Contracts\Repositories\ProfileDocumentRepositoryInterface;
use Modules\Profile\Models\ProfileDocument;
use Illuminate\Http\UploadedFile;

class DocumentService extends BaseService
{
    protected array $allowedGuards = ['api', 'psw-api'];
    protected DocumentTypeRepositoryInterface $documentTypeRepository;
    protected ProfileDocumentRepositoryInterface $profileDocumentRepository;
    protected FileStorageService $fileStorageService;

    public function __construct(
        DocumentTypeRepositoryInterface $documentTypeRepository,
        ProfileDocumentRepositoryInterface $profileDocumentRepository,
        FileStorageService $fileStorageService
    ) {
        $this->documentTypeRepository = $documentTypeRepository;
        $this->profileDocumentRepository = $profileDocumentRepository;
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Get all active document types for user/psw
     *
     * @return array
     */
    public function getDocumentTypes(): array
    {
        $documentTypes = $this->documentTypeRepository->getActive();

        return $this->success(
            $documentTypes,
            'Document types retrieved successfully.'
        );
    }

    /**
     * Store or update a document (supports front, back, or both)
     *
     * @param int $documentTypeId
     * @param UploadedFile|null $frontFile
     * @param UploadedFile|null $backFile
     * @param array|null $metadata
     * @return array
     */
    public function storeOrUpdateDocument(
        int $documentTypeId,
        ?UploadedFile $frontFile = null,
        ?UploadedFile $backFile = null,
        ?array $metadata = null
    ): array {
        return $this->executeWithTransaction(function () use ($documentTypeId, $frontFile, $backFile, $metadata) {
            // Get authenticated user
            $authenticatedUser = $this->getAuthenticatedUserOrFail($this->allowedGuards);

            // Get document type
            $documentType = $this->documentTypeRepository->find($documentTypeId);
            
            if (!$documentType) {
                $this->fail('Document type not found.', 404);
            }

            // Validate document type is active
            if (!$documentType['active']) {
                $this->fail('This document type is not available for upload.', 400);
            }

            // Validate at least one file is provided
            if (!$frontFile && !$backFile) {
                $this->fail('At least one file (front or back) must be provided.', 422);
            }

            // Determine documentable type
            $documentableType = get_class($authenticatedUser);
            $documentableId = $authenticatedUser->id;

            // Find or create profile document record
            $existingDocument = $this->profileDocumentRepository->findByDocumentableAndType(
                $documentableType,
                $documentableId,
                $documentTypeId
            );

            $documentData = [
                'documentable_type' => $documentableType,
                'documentable_id' => $documentableId,
                'document_type_id' => $documentTypeId,
                'status' => 'pending',
                'uploaded_by_type' => get_class($authenticatedUser),
                'uploaded_by_id' => $authenticatedUser->id,
                'metadata' => $metadata ?? [],
            ];

            if ($existingDocument) {
                $document = $this->profileDocumentRepository->update($existingDocument['id'], $documentData);
            } else {
                $document = $this->profileDocumentRepository->create($documentData);
            }

            // Get ProfileDocument model instance
            $profileDocument = ProfileDocument::find($document['id']);

            $results = [];

            // Upload front file if provided
            if ($frontFile) {
                $this->validateDocumentFile($frontFile, $documentType);
                
                // Delete existing front file if any
                $existingFrontFile = $profileDocument->files()->where('file_type', 'document_front')->first();
                if ($existingFrontFile) {
                    $this->fileStorageService->deleteFile($existingFrontFile->id);
                }

                $frontResult = $this->uploadFile($profileDocument, $frontFile, 'document_front', $documentType);
                $results['front'] = $frontResult;
            }

            // Upload back file if provided
            if ($backFile) {
                $this->validateDocumentFile($backFile, $documentType);
                
                // Delete existing back file if any
                $existingBackFile = $profileDocument->files()->where('file_type', 'document_back')->first();
                if ($existingBackFile) {
                    $this->fileStorageService->deleteFile($existingBackFile->id);
                }

                $backResult = $this->uploadFile($profileDocument, $backFile, 'document_back', $documentType);
                $results['back'] = $backResult;
            }

            // Get updated document with file info
            $updatedDocument = $this->profileDocumentRepository->find($document['id']);

            $message = [];
            if ($frontFile && $backFile) {
                $message[] = 'Both front and back documents uploaded successfully.';
            } elseif ($frontFile) {
                $message[] = 'Front document uploaded successfully.';
            } elseif ($backFile) {
                $message[] = 'Back document uploaded successfully.';
            }

            return $this->success(
                $updatedDocument,
                implode(' ', $message),
                $existingDocument ? 200 : 201
            );
        });
    }

    /**
     * Upload a file to storage
     *
     * @param ProfileDocument $profileDocument
     * @param UploadedFile $file
     * @param string $fileType
     * @param array $documentType
     * @return array
     */
    protected function uploadFile(
        ProfileDocument $profileDocument,
        UploadedFile $file,
        string $fileType,
        array $documentType
    ): array {
        $fileOptions = [
            'allowed_mime' => $documentType['allowed_mime'],
            'max_size' => $documentType['max_size_kb'] * 1024,
            'file_type' => $fileType,
        ];

        $fileResult = $this->fileStorageService->storeFile(
            file: $file,
            owner: $profileDocument,
            options: $fileOptions
        );

        if (!$fileResult['id']) {
            $this->fail('Failed to upload document file.', 500);
        }

        return $fileResult;
    }

    /**
     * Get all documents for authenticated user with optional status filter
     * Returns all document types with user's upload status for each
     * For regular users, only returns proof_of_id document as object
     * For PSWs, returns all documents as array
     *
     * @param string|null $status
     * @param string|null $documentKey Filter by specific document key (e.g., 'proof_of_id' for users)
     * @return array
     */
    public function getAllDocuments(?string $status = null, ?string $documentKey = null): array
    {
        // Get authenticated user
        $authenticatedUser = $this->getAuthenticatedUserOrFail($this->allowedGuards);

        // Determine documentable type
        $documentableType = get_class($authenticatedUser);
        $documentableId = $authenticatedUser->id;

        // Get all document types with user's upload status
        $documents = $this->profileDocumentRepository->getAllDocumentTypesWithUserStatus(
            $documentableType,
            $documentableId,
            $status,
            $documentKey
        );

        // If documentKey is provided (user case), return single object instead of array
        if ($documentKey) {
            $document = !empty($documents) ? $documents[0] : null;
            return $this->success(
                $document,
                'Document retrieved successfully.'
            );
        }

        return $this->success(
            $documents,
            'Documents retrieved successfully.'
        );
    }

    /**
     * Get single document by document type ID with files
     *
     * @param int $documentTypeId
     * @return array
     */
    public function getDocumentByType(int $documentTypeId): array
    {
        // Get authenticated user
        $authenticatedUser = $this->getAuthenticatedUserOrFail($this->allowedGuards);

        // Determine documentable type
        $documentableType = get_class($authenticatedUser);
        $documentableId = $authenticatedUser->id;

        // Get document with files
        $document = $this->profileDocumentRepository->getDocumentByTypeWithFiles(
            $documentableType,
            $documentableId,
            $documentTypeId
        );

        if (!$document) {
            $this->fail('Document type not found or inactive.', 404);
        }

        return $this->success(
            $document,
            'Document retrieved successfully.'
        );
    }

    /**
     * Get all documents for a specific user/PSW by ID (for admin use)
     * Public method that doesn't require authentication
     *
     * @param object $documentable The user or PSW model instance
     * @param string|null $status Optional status filter
     * @return array
     */
    public function getDocumentsByDocumentable(object $documentable, ?string $status = null): array
    {
        // Get documentable type and id
        $documentableType = get_class($documentable);
        $documentableId = $documentable->id;

        // Get all document types with user's upload status and files
        $documents = $this->profileDocumentRepository->getAllDocumentTypesWithUserStatusAndFiles(
            $documentableType,
            $documentableId,
            $status
        );

        return $documents;
    }

    /**
     * Validate document file against document type rules
     *
     * @param UploadedFile $file
     * @param array $documentType
     * @return void
     */
    protected function validateDocumentFile(UploadedFile $file, array $documentType): void
    {
        // Check mime type
        $allowedMimes = $documentType['allowed_mime'] ?? [];
        if (!empty($allowedMimes) && !in_array($file->getMimeType(), $allowedMimes)) {
            $this->fail(
                'File type not allowed. Allowed types: ' . implode(', ', $allowedMimes),
                422
            );
        }

        // Check file size (convert KB to bytes)
        $maxSizeBytes = ($documentType['max_size_kb'] ?? 5120) * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            $maxSizeMB = round($maxSizeBytes / 1024 / 1024, 2);
            $this->fail("File size cannot exceed {$maxSizeMB}MB.", 422);
        }
    }
}
