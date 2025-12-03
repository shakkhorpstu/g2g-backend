<?php

namespace App\Shared\Services;

use App\Shared\Contracts\Repositories\FileStorageRepositoryInterface;
use App\Shared\Models\FileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class FileStorageService extends BaseService
{
    protected FileStorageRepositoryInterface $fileStorageRepository;
    protected array $allowedGuards = ['api', 'psw-api', 'admin-api'];
    protected string $disk = 'do_spaces';

    public function __construct(FileStorageRepositoryInterface $fileStorageRepository)
    {
        $this->fileStorageRepository = $fileStorageRepository;
    }

    /**
     * Store a file with polymorphic relationship
     *
     * @param UploadedFile $file
     * @param Model $owner
     * @param string $fileType
     * @param string|null $fileCategory
     * @param array $options
     * @return array
     */
    public function storeFile(
        UploadedFile $file,
        Model $owner,
        array $options = []
    ): array {
        return $this->executeWithTransaction(function () use ($file, $owner, $options) {
            // Validate file using options if provided (allowed_mime, max_size)
            $this->validateFile($file, $options);

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $storedName = $this->generateUniqueFileName($extension);

            // Build organized file path
            $filePath = $this->buildFilePath($owner, $storedName);

            // Store file in Digital Ocean Spaces
            $uploaded = Storage::disk($this->disk)->put($filePath, file_get_contents($file));

            if (!$uploaded) {
                $this->fail('Failed to upload file to storage', 500);
            }

            // Get authenticated user as uploader
            $uploader = $this->getAuthenticatedUser($this->allowedGuards);

            // Prepare file record data
            $fileData = [
                'fileable_type' => get_class($owner),
                'fileable_id' => $owner->id,
                'file_type' => $options['file_type'] ?? null,
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $storedName,
                'file_path' => $filePath,
                // 'file_url' => $this->getUrl($filePath),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by_type' => $uploader ? get_class($uploader) : null,
                'uploaded_by_id' => $uploader ? $uploader->id : null,
                'metadata' => $this->extractMetadata($file),
                'expires_at' => $options['expires_at'] ?? null,
            ];

            // Create file record
            $fileRecord = $this->fileStorageRepository->create($fileData);
            if($fileRecord) {
                $fileRecord->setAttribute('file_url', $this->getUrl($filePath));
            }

            return $fileRecord->toArray();
        });
    }

    public function getUrl(string $filePath): string
    {
        return rtrim(env('DO_SPACES_URL'), '/') . '/' . trim($filePath, '/');
    }

    /**
     * Get file by ID
     *
     * @param int $id
     * @return array
     */
    public function getFile(int $id): array
    {
        $file = $this->fileStorageRepository->findById($id);

        if (!$file) {
            $this->fail('File not found', 404);
        }

        return $this->success([
            'file' => $file->toArray(),
            'download_url' => $file->is_public ? $file->full_url : null,
        ], 'File retrieved successfully');
    }

    /**
     * Get files for owner
     *
     * @param Model $owner
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getFilesForOwner(Model $owner, array $filters = [], int $perPage = 15): array
    {
        if ($perPage > 0) {
            $files = $this->fileStorageRepository->getPaginatedForOwner($owner, $perPage, $filters);
        } else {
            $files = $this->fileStorageRepository->getForOwner($owner, $filters);
        }

        return $this->success([
            'files' => $files,
            'statistics' => $this->fileStorageRepository->getStatisticsForOwner($owner),
        ], 'Files retrieved successfully');
    }

    /**
     * Update file information
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateFile(int $id, array $data): array
    {
        return $this->executeWithTransaction(function () use ($id, $data) {
            $file = $this->fileStorageRepository->findById($id);

            if (!$file) {
                $this->fail('File not found', 404);
            }

            // Validate ownership or permission
            $this->validateFileOwnership($file);

            // Filter allowed fields for update
            $allowedFields = [
                'file_category',
                'is_public',
                'metadata',
                'expires_at'
            ];

            $updateData = array_intersect_key($data, array_flip($allowedFields));

            // Note: no enum-based category validation — caller should ensure valid category if needed

            $updatedFile = $this->fileStorageRepository->update($file, $updateData);

            return $this->success([
                'file' => $updatedFile->toArray()
            ], 'File updated successfully');
        });
    }

    /**
     * Verify file
     *
     * @param int $id
     * @return array
     */
    public function verifyFile(int $id): array
    {
        return $this->executeWithTransaction(function () use ($id) {
            $file = $this->fileStorageRepository->findById($id);

            if (!$file) {
                $this->fail('File not found', 404);
            }

            $verifiedFile = $this->fileStorageRepository->markAsVerified($file);

            return $this->success([
                'file' => $verifiedFile->toArray()
            ], 'File verified successfully');
        });
    }

    /**
     * Delete file
     *
     * @param int $id
     * @return array
     */
    public function deleteFile(int $id): array
    {
        return $this->executeWithTransaction(function () use ($id) {
            $file = $this->fileStorageRepository->findById($id);

            if (!$file) {
                $this->fail('File not found', 404);
            }

            // Validate ownership or permission
            $this->validateFileOwnership($file);

            // Delete file from storage
            if ($file->existsInStorage()) {
                Storage::disk($this->disk)->delete($file->file_path);
            }

            // Delete record
            $this->fileStorageRepository->delete($file);

            return $this->success(null, 'File deleted successfully');
        });
    }

    /**
     * Get download URL for file
     *
     * @param int $id
     * @param int $expiryMinutes
     * @return array
     */
    public function getDownloadUrl(int $id, int $expiryMinutes = 30): array
    {
        $file = $this->fileStorageRepository->findById($id);

        if (!$file) {
            $this->fail('File not found', 404);
        }

        // Validate ownership or permission for private files
        if (!$file->is_public) {
            $this->validateFileOwnership($file);
        }

        // For public files, return the public URL
        if ($file->is_public) {
            $downloadUrl = $file->full_url;
        } else {
            // For private files, generate a temporary signed URL
            try {
                $downloadUrl = Storage::disk($this->disk)->temporaryUrl(
                    $file->file_path,
                    now()->addMinutes($expiryMinutes)
                );
            } catch (\Exception $e) {
                // Fallback if temporary URLs are not supported
                $downloadUrl = Storage::disk($this->disk)->url($file->file_path);
            }
        }

        return $this->success([
            'download_url' => $downloadUrl,
            'expires_in_minutes' => $file->is_public ? null : $expiryMinutes,
            'file_info' => [
                'name' => $file->original_name,
                'size' => $file->human_file_size,
                'type' => $file->mime_type,
            ]
        ], 'Download URL generated successfully');
    }

    /**
     * Clean up expired files
     *
     * @return array
     */
    public function cleanupExpiredFiles(): array
    {
        $deletedCount = $this->fileStorageRepository->deleteExpiredFiles();

        return $this->success([
            'deleted_count' => $deletedCount
        ], "Cleaned up {$deletedCount} expired files");
    }

    /**
     * Validate file type
     *
     * @param string $fileType
     */
    // File type enum removed; validation should be provided via $options in storeFile


    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @param string $fileType
     */
    private function validateFile(UploadedFile $file, array $options = []): void
    {
        // Validate MIME types if provided in options (array of strings)
        if (isset($options['allowed_mime']) && is_array($options['allowed_mime'])) {
            $allowed = $options['allowed_mime'];
            if (!in_array($file->getMimeType(), $allowed)) {
                $this->fail('Invalid file type. Allowed types: ' . implode(', ', $allowed), 422);
            }
        }

        // Validate max size if provided (bytes or kilobytes)
        if (isset($options['max_size_kb']) || isset($options['max_size'])) {
            $maxKb = $options['max_size_kb'] ?? $options['max_size'];
            $maxBytes = (int)$maxKb * 1024;
            if ($file->getSize() > $maxBytes) {
                $maxSizeMB = round($maxBytes / (1024 * 1024), 2);
                $this->fail("File size exceeds maximum allowed size of {$maxSizeMB}MB", 422);
            }
        }
    }

    /**
     * Generate unique filename
     *
     * @param string $extension
     * @return string
     */
    private function generateUniqueFileName(string $extension): string
    {
        do {
            $filename = Str::uuid() . '.' . $extension;
        } while ($this->fileStorageRepository->findByStoredName($filename));

        return $filename;
    }

    /**
     * Build organized file path
     *
     * @param string $fileType
     * @param Model $owner
     * @param string $filename
     * @return string
     */
    private function buildFilePath(Model $owner, string $filename): string
    {
        $ownerType = strtolower(class_basename($owner));
        $ownerId = $owner->id;
        $yearMonth = now()->format('Y/m');

        return "{$ownerType}/{$ownerId}/{$yearMonth}/{$filename}";
    }

    /**
     * Extract file metadata
     *
     * @param UploadedFile $file
     * @return array
     */
    private function extractMetadata(UploadedFile $file): array
    {
        $metadata = [
            'original_extension' => $file->getClientOriginalExtension(),
        ];

        // Extract image dimensions if it's an image
        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                $imageInfo = getimagesize($file->getPathname());
                if ($imageInfo) {
                    $metadata['width'] = $imageInfo[0];
                    $metadata['height'] = $imageInfo[1];
                    $metadata['aspect_ratio'] = round($imageInfo[0] / $imageInfo[1], 2);
                }
            } catch (\Exception $e) {
                // Ignore errors in image processing
            }
        }

        return $metadata;
    }

    /**
     * Validate file ownership
     *
     * @param FileStorage $file
     */
    private function validateFileOwnership(FileStorage $file): void
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            $this->fail('Authentication required', 401);
        }

        // Check if user owns the file or is an admin
        $isOwner = $file->fileable_type === get_class($user) && $file->fileable_id === $user->id;
        $isUploader = $file->uploaded_by_type === get_class($user) && $file->uploaded_by_id === $user->id;
        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('admin');

        if (!$isOwner && !$isUploader && !$isAdmin) {
            $this->fail('You do not have permission to access this file', 403);
        }
    }
}