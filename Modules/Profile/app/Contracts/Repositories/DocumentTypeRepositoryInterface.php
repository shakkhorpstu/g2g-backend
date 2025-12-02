<?php

namespace Modules\Profile\Contracts\Repositories;

interface DocumentTypeRepositoryInterface
{
    /**
     * Get all document types
     *
     * @param bool $activeOnly
     * @return array
     */
    public function getAll(bool $activeOnly = false): array;

    /**
     * Find document type by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array;

    /**
     * Find document type by key
     *
     * @param string $key
     * @return array|null
     */
    public function findByKey(string $key): ?array;

    /**
     * Create new document type
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array;

    /**
     * Update document type
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array;

    /**
     * Delete document type
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get active document types
     *
     * @return array
     */
    public function getActive(): array;
}
