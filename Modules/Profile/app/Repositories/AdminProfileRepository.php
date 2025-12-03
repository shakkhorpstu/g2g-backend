<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Contracts\Repositories\AdminProfileRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Shared\Services\FileStorageService;

class AdminProfileRepository implements AdminProfileRepositoryInterface
{
    protected FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Get admin with profile picture
     *
     * @param int $adminId
     * @return array|null
     */
    public function getAdminWithProfile(int $adminId): ?array
    {
        $result = DB::table('admins')
            ->leftJoin('file_storages', function ($join) {
                $join->on('admins.id', '=', 'file_storages.fileable_id')
                    ->where('file_storages.fileable_type', '=', 'Modules\Core\Models\Admin')
                    ->whereRaw('file_storages.id = (
                        SELECT id FROM file_storages fs 
                        WHERE fs.fileable_id = admins.id 
                        AND fs.fileable_type = ? 
                        ORDER BY fs.created_at DESC 
                        LIMIT 1
                    )', ['Modules\Core\Models\Admin']);
            })
            ->where('admins.id', $adminId)
            ->select(
                'admins.id',
                'admins.name',
                'admins.email',
                'admins.status',
                'admins.last_login_at',
                'admins.created_at as admin_created_at',
                'admins.updated_at as admin_updated_at',
                'file_storages.id as picture_id',
                'file_storages.file_path as picture_path',
                'file_storages.original_name as picture_original_name',
                'file_storages.mime_type as picture_mime_type',
                'file_storages.file_size as picture_file_size',
                'file_storages.created_at as picture_created_at'
            )
            ->first();

        if (!$result) {
            return null;
        }

        // Map the flat result to nested structure
        return [
            'id' => $result->id,
            'name' => $result->name,
            'email' => $result->email,
            'status' => $result->status,
            'last_login_at' => $result->last_login_at,
            'created_at' => $result->admin_created_at,
            'updated_at' => $result->admin_updated_at,
            'profile_picture' => $result->picture_id ? [
                'id' => $result->picture_id,
                'file_path' => $result->picture_path,
                'file_url' => $this->fileStorageService->getUrl($result->picture_path),
                'original_name' => $result->picture_original_name,
                'mime_type' => $result->picture_mime_type,
                'file_size' => $result->picture_file_size,
                'created_at' => $result->picture_created_at,
            ] : null,
        ];
    }
}
