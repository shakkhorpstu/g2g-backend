<?php

namespace Modules\Profile\Contracts\Repositories;

interface ProfileDocumentRepositoryInterface
{
    /**
     * Get all documents by documentable (User/Psw)
     *
     * @param string $documentableType
     * @param int $documentableId
     * @param string|null $status
     * @return array
     */
    public function getByDocumentable(string $documentableType, int $documentableId, ?string $status = null): array;

    /**
     * Find document by documentable and type
     *
     * @param string $documentableType
     * @param int $documentableId
     * @param int $documentTypeId
     * @param string|null $documentSideKey
     * @return array|null
     */
    public function findByDocumentableAndType(
        string $documentableType,
        int $documentableId,
        int $documentTypeId,
        ?string $documentSideKey = null
    ): ?array;

    /**
     * Create new document
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array;

    /**
     * Update document
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array;

    /**
     * Update document status
     *
     * @param int $id
     * @param string $status
     * @param int|null $verifiedById
     * @param string|null $adminNotes
     * @return array
     */
    public function updateStatus(int $id, string $status, ?int $verifiedById = null, ?string $adminNotes = null): array;

    /**
     * Delete document
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get documents by status
     *
     * @param string $status
     * @return array
     */
    public function getByStatus(string $status): array;

    /**
     * Find document by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array;
}
