<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Contracts\Repositories\UserProfileRepositoryInterface;
use Modules\Profile\Models\UserProfile;
use Modules\Core\Models\User;
use Illuminate\Support\Facades\DB;
use App\Shared\Services\FileStorageService;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    protected FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }
    /**
     * Find user profile by user ID
     *
     * @param int $userId
     * @return UserProfile|null
     */
    public function findByUserId(int $userId): ?UserProfile
    {
        return UserProfile::where('user_id', $userId)->first();
    }

    /**
     * Create user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
    public function create(int $userId, array $data): UserProfile
    {
        $data['user_id'] = $userId;
        return UserProfile::create($data);
    }

    /**
     * Update user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
    public function update(int $userId, array $data): UserProfile
    {
        $profile = $this->findByUserId($userId);
        
        if (!$profile) {
            throw new \Exception('User profile not found');
        }

        $profile->update($data);
        return $profile->fresh();
    }

    /**
     * Update or create user profile
     *
     * @param int $userId
     * @param array $data
     * @return UserProfile
     */
     public function updateOrCreate(int $userId, array $data): User
    {
        return User::updateOrCreate(
            ['id' => $userId],
            $data
        );
    }

    /**
     * Delete user profile
     *
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId): bool
    {
        $profile = $this->findByUserId($userId);
        
        if (!$profile) {
            return false;
        }

        return $profile->delete();
    }

    /**
     * Get user with profile and profile picture
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserWithProfile(int $userId): ?array
    {
        $result = DB::table('users')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin('file_storages', function ($join) {
                $join->on('users.id', '=', 'file_storages.fileable_id')
                    ->where('file_storages.fileable_type', '=', 'Modules\Core\Models\User')
                    ->whereRaw('file_storages.id = (
                        SELECT id FROM file_storages fs 
                        WHERE fs.fileable_id = users.id 
                        AND fs.fileable_type = ? 
                        ORDER BY fs.created_at DESC 
                        LIMIT 1
                    )', ['Modules\Core\Models\User']);
            })
            ->where('users.id', $userId)
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.phone_number',
                'users.gender',
                'users.is_verified',
                'users.email_verified_at',
                'users.status',
                'users.last_login_at',
                'users.created_at as user_created_at',
                'users.updated_at as user_updated_at',
                'user_profiles.id as profile_id',
                'user_profiles.user_id as profile_user_id',
                'user_profiles.created_at as profile_created_at',
                'user_profiles.updated_at as profile_updated_at',
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

        // Get default address
        $defaultAddress = DB::table('addresses')
            ->where('addressable_type', 'Modules\\Core\\Models\\User')
            ->where('addressable_id', $userId)
            ->where('is_default', true)
            ->first();

        // Map the flat result to nested structure
        return [
            'id' => $result->id,
            'first_name' => $result->first_name,
            'last_name' => $result->last_name,
            'email' => $result->email,
            'phone_number' => $result->phone_number,
            'gender' => $result->gender,
            'is_verified' => (bool) $result->is_verified,
            'email_verified_at' => $result->email_verified_at,
            'status' => $result->status,
            'last_login_at' => $result->last_login_at,
            'created_at' => $result->user_created_at,
            'updated_at' => $result->user_updated_at,
            'profile' => $result->profile_id ? [
                'id' => $result->profile_id,
                'user_id' => $result->profile_user_id,
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
            'default_address' => $defaultAddress ? [
                'id' => $defaultAddress->id,
                'label' => $defaultAddress->label,
                'address_line' => $defaultAddress->address_line,
                'city' => $defaultAddress->city,
                'province' => $defaultAddress->province,
                'postal_code' => $defaultAddress->postal_code,
                'country_id' => $defaultAddress->country_id,
                'latitude' => $defaultAddress->latitude,
                'longitude' => $defaultAddress->longitude,
                'is_default' => (bool) $defaultAddress->is_default,
                'created_at' => $defaultAddress->created_at,
                'updated_at' => $defaultAddress->updated_at,
            ] : null,
        ];
    }
}