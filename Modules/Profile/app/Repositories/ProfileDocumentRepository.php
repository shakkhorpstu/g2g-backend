<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Contracts\Repositories\ProfileDocumentRepositoryInterface;
use Modules\Profile\Models\ProfileDocument;
use Illuminate\Support\Facades\DB;
use App\Shared\Services\FileStorageService;

class ProfileDocumentRepository implements ProfileDocumentRepositoryInterface
{
    protected FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Get all documents by documentable (User/Psw)
     *
     * @param string $documentableType
     * @param int $documentableId
     * @param string|null $status
     * @return array
     */
    public function getByDocumentable(string $documentableType, int $documentableId, ?string $status = null): array
    {
        $query = DB::table('profile_documents as pd')
            ->leftJoin('document_types as dt', 'pd.document_type_id', '=', 'dt.id')
            ->where('pd.documentable_type', $documentableType)
            ->where('pd.documentable_id', $documentableId)
            ->select('pd.*', 'dt.title as document_type_title', 'dt.key as document_type_key', 'dt.both_sided');

        if ($status) {
            $query->where('pd.status', $status);
        }

        $query->orderBy('dt.sort_order', 'asc')->orderBy('pd.created_at', 'desc');

        $documents = $query->get();

        return $documents->map(function ($doc) {
            return $this->mapToArrayWithFiles($doc);
        })->toArray();
    }

    /**
     * Find document by documentable and type
     *
     * @param string $documentableType
     * @param int $documentableId
     * @param int $documentTypeId
     * @return array|null
     */
    public function findByDocumentableAndType(
        string $documentableType,
        int $documentableId,
        int $documentTypeId
    ): ?array {
        $row = DB::table('profile_documents as pd')
            ->leftJoin('document_types as dt', 'pd.document_type_id', '=', 'dt.id')
            ->where('pd.documentable_type', $documentableType)
            ->where('pd.documentable_id', $documentableId)
            ->where('pd.document_type_id', $documentTypeId)
            ->select('pd.*', 'dt.title as document_type_title', 'dt.key as document_type_key', 'dt.both_sided')
            ->first();

        return $row ? $this->mapToArrayWithFiles($row) : null;
    }

    /**
     * Create new document
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }

        $data['created_at'] = now();
        $data['updated_at'] = now();

        $id = DB::table('profile_documents')->insertGetId($data);

        return $this->find($id);
    }

    /**
     * Update document
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }

        $data['updated_at'] = now();

        DB::table('profile_documents')->where('id', $id)->update($data);

        return $this->find($id);
    }

    /**
     * Update document status
     *
     * @param int $id
     * @param string $status
     * @param int|null $verifiedById
     * @param string|null $adminNotes
     * @return array
     */
    public function updateStatus(int $id, string $status, ?int $verifiedById = null, ?string $adminNotes = null): array
    {
        $data = [
            'status' => $status,
            'updated_at' => now(),
        ];

        if ($status === 'verified' && $verifiedById) {
            $data['verified_by_id'] = $verifiedById;
            $data['verified_at'] = now();
        }

        if ($adminNotes) {
            $data['admin_notes'] = $adminNotes;
        }

        DB::table('profile_documents')->where('id', $id)->update($data);

        return $this->find($id);
    }

    /**
     * Delete document
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return DB::table('profile_documents')->where('id', $id)->delete() > 0;
    }

    /**
     * Get documents by status
     *
     * @param string $status
     * @return array
     */
    public function getByStatus(string $status): array
    {
        $documents = DB::table('profile_documents as pd')
            ->leftJoin('document_types as dt', 'pd.document_type_id', '=', 'dt.id')
            ->where('pd.status', $status)
            ->select('pd.*', 'dt.title as document_type_title', 'dt.key as document_type_key', 'dt.both_sided')
            ->orderBy('pd.created_at', 'desc')
            ->get();

        return $documents->map(function ($doc) {
            return $this->mapToArrayWithFiles($doc);
        })->toArray();
    }

    /**
     * Find document by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $row = DB::table('profile_documents as pd')
            ->leftJoin('document_types as dt', 'pd.document_type_id', '=', 'dt.id')
            ->where('pd.id', $id)
            ->select('pd.*', 'dt.title as document_type_title', 'dt.key as document_type_key', 'dt.both_sided')
            ->first();

        return $row ? $this->mapToArrayWithFiles($row) : null;
    }

    /**
     * Map database row to array with files
     *
     * @param object $row
     * @return array
     */
    protected function mapToArrayWithFiles(object $row): array
    {
        // Get all files for this document
        $files = DB::table('file_storages')
            ->where('fileable_type', 'Modules\Profile\Models\ProfileDocument')
            ->where('fileable_id', $row->id)
            ->whereIn('file_type', ['document_front', 'document_back'])
            ->get();

        $frontFile = null;
        $backFile = null;

        foreach ($files as $file) {
            $fileData = [
                'id' => $file->id,
                'name' => $file->original_name,
                'url' => $this->fileStorageService->getUrl($file->file_path),
                'mime_type' => $file->mime_type,
                'size' => $file->file_size,
                'uploaded_at' => $file->created_at,
            ];

            if ($file->file_type === 'document_front') {
                $frontFile = $fileData;
            } elseif ($file->file_type === 'document_back') {
                $backFile = $fileData;
            }
        }

        return [
            'id' => $row->id,
            'documentable_type' => $row->documentable_type,
            'documentable_id' => $row->documentable_id,
            'document_type_id' => $row->document_type_id,
            'document_type_title' => $row->document_type_title ?? null,
            'document_type_key' => $row->document_type_key ?? null,
            'both_sided' => isset($row->both_sided) ? (bool) $row->both_sided : false,
            'status' => $row->status,
            'uploaded_by_type' => $row->uploaded_by_type,
            'uploaded_by_id' => $row->uploaded_by_id,
            'verified_by_id' => $row->verified_by_id,
            'verified_at' => $row->verified_at,
            'metadata' => json_decode($row->metadata ?? '{}', true),
            'admin_notes' => $row->admin_notes,
            'front_file' => $frontFile,
            'back_file' => $backFile,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }

    /**
     * Get all document types with user's upload status
     *
     * @param string $documentableType
     * @param int $documentableId
     * @param string|null $status
     * @param string|null $documentKey
     * @return array
     */
    public function getAllDocumentTypesWithUserStatus(string $documentableType, int $documentableId, ?string $status = null, ?string $documentKey = null): array
    {
        $query = DB::table('document_types as dt')
            ->leftJoin('profile_documents as pd', function ($join) use ($documentableType, $documentableId) {
                $join->on('dt.id', '=', 'pd.document_type_id')
                    ->where('pd.documentable_type', '=', $documentableType)
                    ->where('pd.documentable_id', '=', $documentableId);
            })
            ->where('dt.active', true)
            ->select(
                'dt.id as document_type_id',
                'dt.title as document_type_title',
                'dt.key as document_type_key',
                'dt.both_sided',
                'dt.description',
                'pd.id as profile_document_id',
                'pd.status',
                'pd.documentable_type',
                'pd.documentable_id',
                'pd.uploaded_by_type',
                'pd.uploaded_by_id',
                'pd.verified_by_id',
                'pd.verified_at',
                'pd.metadata',
                'pd.admin_notes',
                'pd.created_at',
                'pd.updated_at'
            );

        if ($status) {
            $query->where('pd.status', $status);
        }

        if ($documentKey) {
            $query->where('dt.key', $documentKey);
        }

        $query->orderBy('dt.sort_order', 'asc');

        $documents = $query->get();

        return $documents->map(function ($row) {
            if ($row->profile_document_id) {
                // User has uploaded this document type
                return [
                    'document_type_id' => $row->document_type_id,
                    'document_type_title' => $row->document_type_title,
                    'document_type_key' => $row->document_type_key,
                    'both_sided' => (bool) $row->both_sided,
                    'description' => $row->description,
                    'profile_document_id' => $row->profile_document_id,
                    'status' => $row->status,
                    'verified_at' => $row->verified_at,
                    'admin_notes' => $row->admin_notes,
                    'uploaded_at' => $row->created_at,
                ];
            } else {
                // User has not uploaded this document type yet
                return [
                    'document_type_id' => $row->document_type_id,
                    'document_type_title' => $row->document_type_title,
                    'document_type_key' => $row->document_type_key,
                    'both_sided' => (bool) $row->both_sided,
                    'description' => $row->description,
                    'profile_document_id' => null,
                    'status' => null,
                    'verified_at' => null,
                    'admin_notes' => null,
                    'uploaded_at' => null,
                ];
            }
        })->toArray();
    }

    /**
     * Get document by document type ID with files
     *
     * @param string $documentableType
     * @param int $documentableId
     * @param int $documentTypeId
     * @return array|null
     */
    public function getDocumentByTypeWithFiles(string $documentableType, int $documentableId, int $documentTypeId): ?array
    {
        $row = DB::table('document_types as dt')
            ->leftJoin('profile_documents as pd', function ($join) use ($documentableType, $documentableId) {
                $join->on('dt.id', '=', 'pd.document_type_id')
                    ->where('pd.documentable_type', '=', $documentableType)
                    ->where('pd.documentable_id', '=', $documentableId);
            })
            ->where('dt.id', $documentTypeId)
            ->where('dt.active', true)
            ->select(
                'dt.id as document_type_id',
                'dt.title as document_type_title',
                'dt.key as document_type_key',
                'dt.both_sided',
                'dt.description',
                'pd.id as profile_document_id',
                'pd.status',
                'pd.verified_at',
                'pd.admin_notes',
                'pd.metadata',
                'pd.created_at',
                'pd.updated_at'
            )
            ->first();

        if (!$row) {
            return null;
        }

        $result = [
            'document_type_id' => $row->document_type_id,
            'document_type_title' => $row->document_type_title,
            'document_type_key' => $row->document_type_key,
            'both_sided' => (bool) $row->both_sided,
            'description' => $row->description,
            'profile_document_id' => $row->profile_document_id,
            'status' => $row->status,
            'verified_at' => $row->verified_at,
            'admin_notes' => $row->admin_notes,
            'metadata' => json_decode($row->metadata ?? '{}', true),
            'uploaded_at' => $row->created_at,
            'front_file' => null,
            'back_file' => null,
        ];

        if ($row->profile_document_id) {
            // Get files if document exists
            $files = DB::table('file_storages')
                ->where('fileable_type', 'Modules\Profile\Models\ProfileDocument')
                ->where('fileable_id', $row->profile_document_id)
                ->whereIn('file_type', ['document_front', 'document_back'])
                ->get();

            foreach ($files as $file) {
                $fileData = [
                    'id' => $file->id,
                    'name' => $file->original_name,
                    'url' => $this->fileStorageService->getUrl($file->file_path),
                    'mime_type' => $file->mime_type,
                    'size' => $file->file_size,
                    'uploaded_at' => $file->created_at,
                ];

                if ($file->file_type === 'document_front') {
                    $result['front_file'] = $fileData;
                } elseif ($file->file_type === 'document_back') {
                    $result['back_file'] = $fileData;
                }
            }
        }

        return $result;
    }

    /**
     * Get all document types with user status and files (for admin use)
     *
     * @param string $documentableType
     * @param int $documentableId
     * @param string|null $status
     * @return array
     */
    public function getAllDocumentTypesWithUserStatusAndFiles(string $documentableType, int $documentableId, ?string $status = null): array
    {
        $query = DB::table('document_types as dt')
            ->leftJoin('profile_documents as pd', function ($join) use ($documentableType, $documentableId) {
                $join->on('dt.id', '=', 'pd.document_type_id')
                    ->where('pd.documentable_type', '=', $documentableType)
                    ->where('pd.documentable_id', '=', $documentableId);
            })
            ->where('dt.active', true)
            ->select(
                'dt.id as document_type_id',
                'dt.title as document_type_title',
                'dt.key as document_type_key',
                'dt.both_sided',
                'dt.description',
                'pd.id as profile_document_id',
                'pd.status',
                'pd.documentable_type',
                'pd.documentable_id',
                'pd.uploaded_by_type',
                'pd.uploaded_by_id',
                'pd.verified_by_id',
                'pd.verified_at',
                'pd.metadata',
                'pd.admin_notes',
                'pd.created_at',
                'pd.updated_at'
            );

        if ($status) {
            $query->where('pd.status', $status);
        }

        $query->orderBy('dt.sort_order', 'asc');

        $documents = $query->get();

        return $documents->map(function ($row) {
            $result = [
                'document_type_id' => $row->document_type_id,
                'document_type_title' => $row->document_type_title,
                'document_type_key' => $row->document_type_key,
                'both_sided' => (bool) $row->both_sided,
                'description' => $row->description,
                'profile_document_id' => $row->profile_document_id,
                'status' => $row->status,
                'verified_at' => $row->verified_at,
                'admin_notes' => $row->admin_notes,
                'metadata' => $row->metadata ? json_decode($row->metadata, true) : null,
                'uploaded_at' => $row->created_at,
                'front_file' => null,
                'back_file' => null,
            ];

            if ($row->profile_document_id) {
                // Get files if document exists
                $files = DB::table('file_storages')
                    ->where('fileable_type', 'Modules\Profile\Models\ProfileDocument')
                    ->where('fileable_id', $row->profile_document_id)
                    ->whereIn('file_type', ['document_front', 'document_back'])
                    ->get();

                foreach ($files as $file) {
                    $fileData = [
                        'id' => $file->id,
                        'name' => $file->original_name,
                        'url' => $this->fileStorageService->getUrl($file->file_path),
                        'mime_type' => $file->mime_type,
                        'size' => $file->file_size,
                        'uploaded_at' => $file->created_at,
                    ];

                    if ($file->file_type === 'document_front') {
                        $result['front_file'] = $fileData;
                    } elseif ($file->file_type === 'document_back') {
                        $result['back_file'] = $fileData;
                    }
                }
            }

            return $result;
        })->toArray();
    }
}
