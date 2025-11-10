<?php

namespace Modules\Profile\Services;

use Modules\Core\Services\BaseService;
use Modules\Core\Exceptions\ServiceException;
use Modules\Core\Models\User;
use Modules\Core\Models\Psw;
use Illuminate\Support\Facades\Auth;
use Modules\Profile\Contracts\Repositories\ProfileRepositoryInterface;

class ProfileService extends BaseService
{
    public function __construct(public ProfileRepositoryInterface $profileRepository)
    {}

    /**
     * Get user profile
     * 
     * @param int|null $userId User ID (if null, gets current authenticated user)
     * @return array Profile data
     * @throws ServiceException When profile retrieval fails
     */
    public function getProfile(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        
        $user = $this->profileRepository->findById($userId);
        
        if (!$user) {
            $this->fail('Profile not found', 404, ['user' => ['Profile not found']]);
        }

        return [
            'data' => [
                'user' => $user
            ],
            'message' => 'Profile retrieved successfully'
        ];
    }

    /**
     * Update user profile
     * 
     * @param array $data Profile update data
     * @param int|null $userId User ID (if null, updates current authenticated user)
     * @return array Updated profile data
     * @throws ServiceException When profile update fails
     */
    public function updateProfile(array $data, ?int $userId = null): array
    {
        return $this->executeWithTransaction(function () use ($data, $userId) {
            $userId = $userId ?? Auth::id();
            
            // Check if user exists
            $existingUser = $this->profileRepository->findById($userId);
            if (!$existingUser) {
                $this->fail('Profile not found', 404, ['user' => ['Profile not found']]);
            }

            // Check if email is being updated and if it already exists
            if (isset($data['email']) && $data['email'] !== $existingUser->email) {
                // Use Eloquent for checking email uniqueness
                if (User::where('email', $data['email'])->where('id', '!=', $userId)->exists()) {
                    $this->fail(
                        'Email already exists',
                        422,
                        ['email' => ['The email has already been taken.']]
                    );
                }
            }

            $user = $this->profileRepository->updateProfile($userId, $data);

            return [
                'data' => [
                    'user' => $user
                ],
                'message' => 'Profile updated successfully'
            ];
        }, 'Profile update failed');
    }

    /**
     * Delete user profile
     * 
     * @param int|null $userId User ID (if null, deletes current authenticated user)
     * @return array Delete result
     * @throws ServiceException When profile deletion fails
     */
    public function deleteProfile(?int $userId = null): array
    {
        return $this->executeWithTransaction(function () use ($userId) {
            $userId = $userId ?? Auth::id();
            
            // Check if user exists
            $user = $this->profileRepository->findById($userId);
            if (!$user) {
                $this->fail('Profile not found', 404, ['user' => ['Profile not found']]);
            }

            $deleted = $this->profileRepository->deleteProfile($userId);
            
            if (!$deleted) {
                $this->fail('Profile deletion failed', 500, ['error' => ['Failed to delete profile']]);
            }

            return [
                'message' => 'Profile deleted successfully'
            ];
        }, 'Profile deletion failed');
    }

    /**
     * Create user profile (admin only)
     * 
     * @param array $data Profile creation data
     * @return array Created profile data
     * @throws ServiceException When profile creation fails
     */
    public function createProfile(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            // Check if email already exists
            if (User::where('email', $data['email'])->exists()) {
                $this->fail(
                    'Email already exists',
                    422,
                    ['email' => ['The email has already been taken.']]
                );
            }

            $user = $this->profileRepository->createProfile($data);

            return [
                'data' => [
                    'user' => $user
                ],
                'message' => 'Profile created successfully',
                'status_code' => 201
            ];
        }, 'Profile creation failed');
    }

    /**
     * Get profile with relationships
     * 
     * @param int|null $userId User ID
     * @param array $relations Relationships to load
     * @return array Profile data with relations
     * @throws ServiceException When profile retrieval fails
     */
    public function getProfileWithRelations(?int $userId = null, array $relations = []): array
    {
        $userId = $userId ?? Auth::id();
        
        $user = $this->profileRepository->getProfileWithRelations($userId, $relations);
        
        if (!$user) {
            $this->fail('Profile not found', 404, ['user' => ['Profile not found']]);
        }

        return [
            'data' => [
                'user' => $user
            ],
            'message' => 'Profile with relations retrieved successfully'
        ];
    }

    /**
     * Get PSW profile
     * 
     * @return array PSW profile data
     * @throws ServiceException When PSW profile retrieval fails
     */
    public function getPswProfile(): array
    {
        $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

        return [
            'data' => [
                'psw' => $psw
            ],
            'message' => 'PSW profile retrieved successfully'
        ];
    }

    /**
     * Update PSW profile
     * 
     * @param array $data PSW profile update data
     * @return array Updated PSW profile data
     * @throws ServiceException When PSW profile update fails
     */
    public function updatePswProfile(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            // Validate unique email if changed
            if (isset($data['email']) && $data['email'] !== $psw->email) {
                if (Psw::where('email', $data['email'])->where('id', '!=', $psw->id)->exists()) {
                    $this->fail('Email already exists', 422, ['email' => ['Email is already taken']]);
                }
            }

            // Update PSW
            $psw->update($data);
            $psw = $psw->fresh();

            return [
                'data' => [
                    'psw' => $psw
                ],
                'message' => 'PSW profile updated successfully'
            ];
        });
    }
}