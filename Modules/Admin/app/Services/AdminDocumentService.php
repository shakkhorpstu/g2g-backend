<?php

namespace Modules\Admin\Services;

use App\Shared\Services\BaseService;
use Modules\Core\Services\UserService;
use Modules\Core\Services\PswService;
use Modules\Profile\Services\DocumentService;

class AdminDocumentService extends BaseService
{
    protected UserService $userService;
    protected PswService $pswService;
    protected DocumentService $documentService;

    public function __construct(
        UserService $userService,
        PswService $pswService,
        DocumentService $documentService
    ) {
        $this->userService = $userService;
        $this->pswService = $pswService;
        $this->documentService = $documentService;
    }

    /**
     * Get all documents for a specific client
     *
     * @param int $userId
     * @param string|null $status
     * @return array
     */
    public function getClientDocuments(int $userId, ?string $status = null): array
    {
        // Check if user exists via Core service
        $user = $this->userService->findById($userId);

        if (!$user) {
            $this->fail('Client not found.', 404);
        }

        // Get documents using Profile DocumentService
        $documents = $this->documentService->getDocumentsByDocumentable($user, $status);

        // Filter out documents that haven't been uploaded (profile_document_id is null)
        $uploadedDocuments = array_values(array_filter($documents, function ($doc) {
            return $doc['profile_document_id'] !== null;
        }));

        return $this->success(
            $uploadedDocuments,
            'Client documents retrieved successfully.'
        );
    }

    /**
     * Get all documents for a specific PSW
     *
     * @param int $pswId
     * @param string|null $status
     * @return array
     */
    public function getPswDocuments(int $pswId, ?string $status = null): array
    {
        // Check if PSW exists via Core service
        $psw = $this->pswService->findById($pswId);

        if (!$psw) {
            $this->fail('PSW not found.', 404);
        }

        // Get documents using Profile DocumentService
        $documents = $this->documentService->getDocumentsByDocumentable($psw, $status);

        // Filter out documents that haven't been uploaded (profile_document_id is null)
        $uploadedDocuments = array_values(array_filter($documents, function ($doc) {
            return $doc['profile_document_id'] !== null;
        }));

        return $this->success(
            $uploadedDocuments,
            'PSW documents retrieved successfully.'
        );
    }
}
