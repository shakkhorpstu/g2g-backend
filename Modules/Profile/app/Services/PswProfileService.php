<?php

namespace Modules\Profile\Services;

use Modules\Profile\Contracts\Repositories\PswProfileRepositoryInterface;
use Modules\Core\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class PswProfileService extends BaseService
{
    /**
     * PSW profile repository instance
     *
     * @var PswProfileRepositoryInterface
     */
    protected PswProfileRepositoryInterface $pswProfileRepository;

    /**
     * PswProfileService constructor
     *
     * @param PswProfileRepositoryInterface $pswProfileRepository
     */
    public function __construct(PswProfileRepositoryInterface $pswProfileRepository)
    {
        $this->pswProfileRepository = $pswProfileRepository;
    }

    /**
     * Get PSW profile
     *
     * @return array
     */
    public function getProfile(): array
    {
        $psw = Auth::guard('psw-api')->user();
        
        if (!$psw) {
            $this->fail('PSW not authenticated', 401);
        }

        $pswWithProfile = $this->pswProfileRepository->getPswWithProfile($psw->id);

        return $this->success([
            'psw' => $pswWithProfile,
            'profile' => $pswWithProfile->profile
        ], 'PSW profile retrieved successfully');
    }

    /**
     * Update PSW profile
     *
     * @param array $data
     * @return array
     */
    public function updateProfile(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = Auth::guard('psw-api')->user();
            
            if (!$psw) {
                $this->fail('PSW not authenticated', 401);
            }

            // Update profile data
            $profileData = array_intersect_key($data, array_flip(['language_id']));
            
            if (!empty($profileData)) {
                $this->pswProfileRepository->updateOrCreate($psw->id, $profileData);
            }

            // Get updated PSW with profile
            $pswWithProfile = $this->pswProfileRepository->getPswWithProfile($psw->id);

            return $this->success([
                'psw' => $pswWithProfile,
                'profile' => $pswWithProfile->profile
            ], 'PSW profile updated successfully');
        });
    }

    /**
     * Create initial profile for PSW
     *
     * @param int $pswId
     * @param array $data
     * @return array
     */
    public function createInitialProfile(int $pswId, array $data = []): array
    {
        return $this->executeWithTransaction(function () use ($pswId, $data) {
            $profileData = array_merge(['language_id' => null], $data);
            
            $profile = $this->pswProfileRepository->create($pswId, $profileData);

            return $this->success($profile, 'PSW profile created successfully');
        });
    }

    /**
     * Delete PSW profile
     *
     * @return array
     */
    public function deleteProfile(): array
    {
        return $this->executeWithTransaction(function () {
            $psw = Auth::guard('psw-api')->user();
            
            if (!$psw) {
                $this->fail('PSW not authenticated', 401);
            }

            $deleted = $this->pswProfileRepository->delete($psw->id);

            if (!$deleted) {
                $this->fail('Profile not found', 404);
            }

            return $this->success(null, 'PSW profile deleted successfully');
        });
    }
}