<?php

namespace Modules\Profile\Services;

use App\Shared\Services\BaseService;
use App\Shared\Services\FileStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class ProfilePictureService extends BaseService
{
    protected array $allowedGuards = ['api', 'psw-api', 'admin-api'];
    protected FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Upload profile picture for authenticated user
     *
     * @param string $userType
     * @param UploadedFile $file
     * @return array
     */
    public function uploadProfilePicture(string $userType, UploadedFile $file): array
    {
        return $this->executeWithTransaction(function () use ($userType, $file) {
            $authenticatedUser = $this->getAuthenticatedUserOrFail($this->allowedGuards);

            if (!$authenticatedUser) {
                $this->fail('Unauthenticated.', 401);
            }

            // Check if profile picture already exists and delete it
            $existingPicture = $authenticatedUser->morphMany(\App\Shared\Models\FileStorage::class, 'fileable')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($existingPicture) {
                // Delete the old profile picture
                $this->fileStorageService->deleteFile($existingPicture->id);
            }

            // Upload new file using FileStorageService
            $result = $this->fileStorageService->storeFile(
                file: $file,
                owner: $authenticatedUser
            );

            if (!$result['id']) {
                $this->fail($result['message'] ?? 'Failed to upload profile picture.', $result['code'] ?? 500);
            }

            return $this->success(
                $result,
                $result['message'] ?? 'Profile picture uploaded successfully.',
                201
            );
        });
    }

    /**
     * Get profile picture for authenticated user
     *
     * @param string $userType
     * @return array
     */
    public function getProfilePicture(string $userType): array
    {
        // Map user type to guard
        $guardMap = [
            'user' => 'api',
            'psw' => 'psw-api',
            'admin' => 'admin-api',
        ];

        // Validate user type
        if (!isset($guardMap[$userType])) {
            $this->fail('Invalid user type. Must be user, psw, or admin.', 400);
        }

        // Get authenticated user
        $guard = $guardMap[$userType];
        $authenticatedUser = Auth::guard($guard)->user();

        if (!$authenticatedUser) {
            $this->fail('Unauthenticated.', 401);
        }

        // Get profile based on user type
        $profile = $this->getProfile($authenticatedUser, $userType);

        // Get latest profile picture for this profile
        $profilePicture = $profile->morphMany(\App\Shared\Models\FileStorage::class, 'fileable')
            ->where('file_type', 'profile_picture')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$profilePicture) {
            $this->fail('No profile picture found.', 404);
        }

        return $this->success([
            'file' => $profilePicture->toArray(),
            'download_url' => $profilePicture->is_public ? $profilePicture->full_url : null,
        ], 'Profile picture retrieved successfully.');
    }

    /**
     * Get profile for authenticated user based on user type
     *
     * @param mixed $authenticatedUser
     * @param string $userType
     * @return mixed
     */
    protected function getProfile($authenticatedUser, string $userType)
    {
        $profileRelation = $userType === 'psw' ? 'pswProfile' : ($userType === 'admin' ? 'adminProfile' : 'userProfile');
        $profile = $authenticatedUser->{$profileRelation} ?? null;

        if (!$profile) {
            // For admin, if no profile model exists, use the admin user itself as the owner
            if ($userType === 'admin') {
                return $authenticatedUser;
            }
            
            $this->fail('Profile not found. Please complete registration.', 404);
        }

        return $profile;
    }
}
