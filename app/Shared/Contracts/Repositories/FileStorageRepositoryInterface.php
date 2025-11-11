<?php

namespace App\Shared\Contracts\Repositories;

use App\Shared\Models\FileStorage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface FileStorageRepositoryInterface
{
    /**
     * Create a new file storage record
     *
     * @param array $data
     * @return FileStorage
     */
    public function create(array $data): FileStorage;

    /**
     * Find file storage by ID
     *
     * @param int $id
     * @return FileStorage|null
     */
    public function findById(int $id): ?FileStorage;

    /**
     * Find file storage by stored name
     *
     * @param string $storedName
     * @return FileStorage|null
     */
    public function findByStoredName(string $storedName): ?FileStorage;

    /**
     * Find file storage by file path
     *
     * @param string $filePath
     * @return FileStorage|null
     */
    public function findByFilePath(string $filePath): ?FileStorage;

    /**
     * Get files for a specific owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return Collection
     */
    public function getForOwner($owner, array $filters = []): Collection;

    /**
     * Get paginated files for a specific owner
     *
     * @param mixed $owner
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedForOwner($owner, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get files by type
     *
     * @param string $fileType
     * @param array $filters
     * @return Collection
     */
    public function getByType(string $fileType, array $filters = []): Collection;

    /**
     * Get files by type for owner
     *
     * @param mixed $owner
     * @param string $fileType
     * @param array $filters
     * @return Collection
     */
    public function getByTypeForOwner($owner, string $fileType, array $filters = []): Collection;

    /**
     * Get files by type and category
     *
     * @param string $fileType
     * @param string $category
     * @param array $filters
     * @return Collection
     */
    public function getByTypeAndCategory(string $fileType, string $category, array $filters = []): Collection;

    /**
     * Get verified files for owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return Collection
     */
    public function getVerifiedForOwner($owner, array $filters = []): Collection;

    /**
     * Get unverified files for owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return Collection
     */
    public function getUnverifiedForOwner($owner, array $filters = []): Collection;

    /**
     * Get public files
     *
     * @param array $filters
     * @return Collection
     */
    public function getPublicFiles(array $filters = []): Collection;

    /**
     * Get expired files
     *
     * @return Collection
     */
    public function getExpiredFiles(): Collection;

    /**
     * Update file storage record
     *
     * @param FileStorage $fileStorage
     * @param array $data
     * @return FileStorage
     */
    public function update(FileStorage $fileStorage, array $data): FileStorage;

    /**
     * Mark file as verified
     *
     * @param FileStorage $fileStorage
     * @return FileStorage
     */
    public function markAsVerified(FileStorage $fileStorage): FileStorage;

    /**
     * Mark file as unverified
     *
     * @param FileStorage $fileStorage
     * @return FileStorage
     */
    public function markAsUnverified(FileStorage $fileStorage): FileStorage;

    /**
     * Update file visibility
     *
     * @param FileStorage $fileStorage
     * @param bool $isPublic
     * @return FileStorage
     */
    public function updateVisibility(FileStorage $fileStorage, bool $isPublic): FileStorage;

    /**
     * Delete file storage record
     *
     * @param FileStorage $fileStorage
     * @return bool
     */
    public function delete(FileStorage $fileStorage): bool;

    /**
     * Delete files for owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return int Number of deleted records
     */
    public function deleteForOwner($owner, array $filters = []): int;

    /**
     * Delete expired files
     *
     * @return int Number of deleted records
     */
    public function deleteExpiredFiles(): int;

    /**
     * Get file storage statistics for owner
     *
     * @param mixed $owner
     * @return array
     */
    public function getStatisticsForOwner($owner): array;

    /**
     * Get total file size for owner
     *
     * @param mixed $owner
     * @param string|null $fileType
     * @return int Size in bytes
     */
    public function getTotalSizeForOwner($owner, ?string $fileType = null): int;

    /**
     * Count files for owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return int
     */
    public function countForOwner($owner, array $filters = []): int;

    /**
     * Check if owner has file of specific type
     *
     * @param mixed $owner
     * @param string $fileType
     * @param string|null $category
     * @return bool
     */
    public function ownerHasFileType($owner, string $fileType, ?string $category = null): bool;

    /**
     * Get latest file by type for owner
     *
     * @param mixed $owner
     * @param string $fileType
     * @param string|null $category
     * @return FileStorage|null
     */
    public function getLatestByTypeForOwner($owner, string $fileType, ?string $category = null): ?FileStorage;
}