<?php

namespace App\Shared\Repositories;

use App\Shared\Contracts\Repositories\FileStorageRepositoryInterface;
use App\Shared\Models\FileStorage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FileStorageRepository implements FileStorageRepositoryInterface
{
    /**
     * Create a new file storage record
     *
     * @param array $data
     * @return FileStorage
     */
    public function create(array $data): FileStorage
    {
        return FileStorage::create($data);
    }

    /**
     * Find file storage by ID
     *
     * @param int $id
     * @return FileStorage|null
     */
    public function findById(int $id): ?FileStorage
    {
        return FileStorage::find($id);
    }

    /**
     * Find file storage by stored name
     *
     * @param string $storedName
     * @return FileStorage|null
     */
    public function findByStoredName(string $storedName): ?FileStorage
    {
        return FileStorage::where('stored_name', $storedName)->first();
    }

    /**
     * Find file storage by file path
     *
     * @param string $filePath
     * @return FileStorage|null
     */
    public function findByFilePath(string $filePath): ?FileStorage
    {
        return FileStorage::where('file_path', $filePath)->first();
    }

    /**
     * Get files for a specific owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return Collection
     */
    public function getForOwner($owner, array $filters = []): Collection
    {
        $query = FileStorage::forOwner($owner)->notExpired();

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get paginated files for a specific owner
     *
     * @param mixed $owner
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedForOwner($owner, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = FileStorage::forOwner($owner)->notExpired();

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    /**
     * Get files by type
     *
     * @param string $fileType
     * @param array $filters
     * @return Collection
     */
    public function getByType(string $fileType, array $filters = []): Collection
    {
        $query = FileStorage::byType($fileType)->notExpired();

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get files by type for owner
     *
     * @param mixed $owner
     * @param string $fileType
     * @param array $filters
     * @return Collection
     */
    public function getByTypeForOwner($owner, string $fileType, array $filters = []): Collection
    {
        $query = FileStorage::forOwner($owner)->byType($fileType)->notExpired();

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get files by type and category
     *
     * @param string $fileType
     * @param string $category
     * @param array $filters
     * @return Collection
     */
    public function getByTypeAndCategory(string $fileType, string $category, array $filters = []): Collection
    {
        $query = FileStorage::byType($fileType)
            ->where('file_category', $category)
            ->notExpired();

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get verified files for owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return Collection
     */
    public function getVerifiedForOwner($owner, array $filters = []): Collection
    {
        $query = FileStorage::forOwner($owner)->verified()->notExpired();

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get unverified files for owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return Collection
     */
    public function getUnverifiedForOwner($owner, array $filters = []): Collection
    {
        $query = FileStorage::forOwner($owner)
            ->where('is_verified', false)
            ->notExpired();

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get public files
     *
     * @param array $filters
     * @return Collection
     */
    public function getPublicFiles(array $filters = []): Collection
    {
        $query = FileStorage::public()->notExpired();

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get expired files
     *
     * @return Collection
     */
    public function getExpiredFiles(): Collection
    {
        return FileStorage::where('expires_at', '<=', now())
            ->whereNotNull('expires_at')
            ->get();
    }

    /**
     * Update file storage record
     *
     * @param FileStorage $fileStorage
     * @param array $data
     * @return FileStorage
     */
    public function update(FileStorage $fileStorage, array $data): FileStorage
    {
        $fileStorage->update($data);
        return $fileStorage->fresh();
    }

    /**
     * Mark file as verified
     *
     * @param FileStorage $fileStorage
     * @return FileStorage
     */
    public function markAsVerified(FileStorage $fileStorage): FileStorage
    {
        $fileStorage->update(['is_verified' => true]);
        return $fileStorage->fresh();
    }

    /**
     * Mark file as unverified
     *
     * @param FileStorage $fileStorage
     * @return FileStorage
     */
    public function markAsUnverified(FileStorage $fileStorage): FileStorage
    {
        $fileStorage->update(['is_verified' => false]);
        return $fileStorage->fresh();
    }

    /**
     * Update file visibility
     *
     * @param FileStorage $fileStorage
     * @param bool $isPublic
     * @return FileStorage
     */
    public function updateVisibility(FileStorage $fileStorage, bool $isPublic): FileStorage
    {
        $fileStorage->update(['is_public' => $isPublic]);
        return $fileStorage->fresh();
    }

    /**
     * Delete file storage record
     *
     * @param FileStorage $fileStorage
     * @return bool
     */
    public function delete(FileStorage $fileStorage): bool
    {
        return $fileStorage->delete();
    }

    /**
     * Delete files for owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return int Number of deleted records
     */
    public function deleteForOwner($owner, array $filters = []): int
    {
        $query = FileStorage::forOwner($owner);
        $query = $this->applyFilters($query, $filters);
        
        return $query->delete();
    }

    /**
     * Delete expired files
     *
     * @return int Number of deleted records
     */
    public function deleteExpiredFiles(): int
    {
        return FileStorage::where('expires_at', '<=', now())
            ->whereNotNull('expires_at')
            ->delete();
    }

    /**
     * Get file storage statistics for owner
     *
     * @param mixed $owner
     * @return array
     */
    public function getStatisticsForOwner($owner): array
    {
        $baseQuery = FileStorage::forOwner($owner)->notExpired();

        return [
            'total_files' => $baseQuery->count(),
            'verified_files' => $baseQuery->clone()->verified()->count(),
            'unverified_files' => $baseQuery->clone()->where('is_verified', false)->count(),
            'public_files' => $baseQuery->clone()->public()->count(),
            'private_files' => $baseQuery->clone()->where('is_public', false)->count(),
            'total_size_bytes' => $baseQuery->sum('file_size'),
            'files_by_type' => $baseQuery->selectRaw('file_type, COUNT(*) as count')
                ->groupBy('file_type')
                ->pluck('count', 'file_type')
                ->toArray(),
        ];
    }

    /**
     * Get total file size for owner
     *
     * @param mixed $owner
     * @param string|null $fileType
     * @return int Size in bytes
     */
    public function getTotalSizeForOwner($owner, ?string $fileType = null): int
    {
        $query = FileStorage::forOwner($owner)->notExpired();

        if ($fileType) {
            $query->byType($fileType);
        }

        return $query->sum('file_size') ?: 0;
    }

    /**
     * Count files for owner
     *
     * @param mixed $owner
     * @param array $filters
     * @return int
     */
    public function countForOwner($owner, array $filters = []): int
    {
        $query = FileStorage::forOwner($owner)->notExpired();

        return $this->applyFilters($query, $filters)->count();
    }

    /**
     * Check if owner has file of specific type
     *
     * @param mixed $owner
     * @param string $fileType
     * @param string|null $category
     * @return bool
     */
    public function ownerHasFileType($owner, string $fileType, ?string $category = null): bool
    {
        $query = FileStorage::forOwner($owner)->byType($fileType)->notExpired();

        if ($category) {
            $query->where('file_category', $category);
        }

        return $query->exists();
    }

    /**
     * Get latest file by type for owner
     *
     * @param mixed $owner
     * @param string $fileType
     * @param string|null $category
     * @return FileStorage|null
     */
    public function getLatestByTypeForOwner($owner, string $fileType, ?string $category = null): ?FileStorage
    {
        $query = FileStorage::forOwner($owner)->byType($fileType)->notExpired()->latest();

        if ($category) {
            $query->where('file_category', $category);
        }

        return $query->first();
    }

    /**
     * Apply filters to query
     *
     * @param mixed $query
     * @param array $filters
     * @return mixed
     */
    private function applyFilters($query, array $filters = [])
    {
        if (!empty($filters['file_type'])) {
            $query->where('file_type', $filters['file_type']);
        }

        if (!empty($filters['file_category'])) {
            $query->where('file_category', $filters['file_category']);
        }

        if (isset($filters['is_verified'])) {
            $query->where('is_verified', $filters['is_verified']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        if (!empty($filters['mime_type'])) {
            if (is_array($filters['mime_type'])) {
                $query->whereIn('mime_type', $filters['mime_type']);
            } else {
                $query->where('mime_type', $filters['mime_type']);
            }
        }

        if (!empty($filters['uploaded_by_type'])) {
            $query->where('uploaded_by_type', $filters['uploaded_by_type']);
        }

        if (!empty($filters['uploaded_by_id'])) {
            $query->where('uploaded_by_id', $filters['uploaded_by_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('file_category', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['order_by'])) {
            $orderBy = $filters['order_by'];
            $orderDirection = $filters['order_direction'] ?? 'asc';
            $query->orderBy($orderBy, $orderDirection);
        } else {
            $query->latest();
        }

        return $query;
    }
}