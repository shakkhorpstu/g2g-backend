<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Contracts\Repositories\PswProfileRepositoryInterface;
use Modules\Profile\Models\PswProfile;
use Modules\Core\Models\Psw;
use Illuminate\Support\Facades\DB;
use App\Shared\Services\FileStorageService;

class PswProfileRepository implements PswProfileRepositoryInterface
{
    protected FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }
    /**
     * Find PSW profile by PSW ID
     *
     * @param int $pswId
     * @return PswProfile|null
     */
    public function findByPswId(int $pswId): ?PswProfile
    {
        return PswProfile::where('psw_id', $pswId)->first();
    }

    /**
     * Create PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function create(int $pswId, array $data): PswProfile
    {
        $data['psw_id'] = $pswId;
        return PswProfile::create($data);
    }

    /**
     * Update PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function update(int $pswId, array $data): PswProfile
    {
        $profile = $this->findByPswId($pswId);
        
        if (!$profile) {
            throw new \Exception('PSW profile not found');
        }

        $profile->update($data);
        return $profile->fresh();
    }

    /**
     * Update or create PSW profile
     *
     * @param int $pswId
     * @param array $data
     * @return PswProfile
     */
    public function updateOrCreate(int $pswId, array $data): Psw
    {
        return Psw::updateOrCreate(
            ['id' => $pswId],
            $data
        );
    }

    /**
     * Delete PSW profile
     *
     * @param int $pswId
     * @return bool
     */
    public function delete(int $pswId): bool
    {
        $profile = $this->findByPswId($pswId);
        
        if (!$profile) {
            return false;
        }

        return $profile->delete();
    }

    /**
     * Get PSW with profile and profile picture
     *
     * @param int $pswId
     * @return array|null
     */
    public function getPswWithProfile(int $pswId): ?array
    {
        $result = DB::table('psws')
            ->leftJoin('psw_profiles', 'psws.id', '=', 'psw_profiles.psw_id')
            ->leftJoin('file_storages', function ($join) {
                $join->on('psws.id', '=', 'file_storages.fileable_id')
                    ->where('file_storages.fileable_type', '=', 'Modules\\Core\\Models\\Psw')
                    ->whereRaw('file_storages.id = (
                        SELECT id FROM file_storages fs 
                        WHERE fs.fileable_id = psws.id 
                        AND fs.fileable_type = ? 
                        ORDER BY fs.created_at DESC 
                        LIMIT 1
                    )', ['Modules\\Core\\Models\\Psw']);
            })
            ->where('psws.id', $pswId)
            ->select(
                'psws.id',
                'psws.first_name',
                'psws.last_name',
                'psws.email',
                'psws.phone_number',
                'psws.gender',
                'psws.is_verified',
                'psws.last_login_at',
                'psws.created_at as psw_created_at',
                'psws.updated_at as psw_updated_at',
                'psw_profiles.id as profile_id',
                'psw_profiles.psw_id as profile_psw_id',
                'psw_profiles.available_status',
                'psw_profiles.hourly_rate',
                'psw_profiles.include_driving_allowance',
                'psw_profiles.driving_allowance_per_km',
                'psw_profiles.has_own_vehicle',
                'psw_profiles.has_wheelchair_accessible_vehicle',
                'psw_profiles.min_booking_slot',
                'psw_profiles.created_at as profile_created_at',
                'psw_profiles.updated_at as profile_updated_at',
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
            'first_name' => $result->first_name,
            'last_name' => $result->last_name,
            'email' => $result->email,
            'phone_number' => $result->phone_number,
            'gender' => $result->gender,
            'is_verified' => (bool) $result->is_verified,
            'last_login_at' => $result->last_login_at,
            'created_at' => $result->psw_created_at,
            'updated_at' => $result->psw_updated_at,
            'profile' => $result->profile_id ? [
                'id' => $result->profile_id,
                'psw_id' => $result->profile_psw_id,
                'available_status' => (bool) $result->available_status,
                'hourly_rate' => $result->hourly_rate,
                'include_driving_allowance' => (bool) $result->include_driving_allowance,
                'driving_allowance_per_km' => $result->driving_allowance_per_km,
                'has_own_vehicle' => (bool) $result->has_own_vehicle,
                'has_wheelchair_accessible_vehicle' => (bool) $result->has_wheelchair_accessible_vehicle,
                'min_booking_slot' => $result->min_booking_slot,
                'created_at' => $result->profile_created_at,
                'updated_at' => $result->profile_updated_at,
            ] : null,
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