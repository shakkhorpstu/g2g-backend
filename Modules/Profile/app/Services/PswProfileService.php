<?php

namespace Modules\Profile\Services;

use Modules\Profile\Contracts\Repositories\PswProfileRepositoryInterface;
use App\Shared\Services\BaseService;
use Modules\Core\Contracts\Repositories\PswRepositoryInterface;
use Modules\Core\Services\OtpService;

class PswProfileService extends BaseService
{
    /**
     * PSW profile repository instance
     *
     * @var PswProfileRepositoryInterface
     */
    protected PswProfileRepositoryInterface $pswProfileRepository;
    protected PswRepositoryInterface $pswRepository;
    protected OtpService $otpService;

    /**
     * PswProfileService constructor
     *
     * @param PswProfileRepositoryInterface $pswProfileRepository
     */
    public function __construct(PswProfileRepositoryInterface $pswProfileRepository, PswRepositoryInterface $pswRepository, OtpService $otpService)
    {
        $this->pswProfileRepository = $pswProfileRepository;
        $this->pswRepository = $pswRepository;
        $this->otpService = $otpService;
    }

    /**
     * Get PSW profile
     *
     * @return array
     */
    public function getProfile(): array
    {
        $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

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
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $pendingChanges = [];

            // Email change request
            if (isset($data['email']) && $data['email'] !== $psw->email) {
                $this->otpService->resendOtp(
                    $data['email'],
                    'email_update',
                    get_class($psw),
                    $psw->id
                );
                $pendingChanges[] = ['type' => 'email', 'value' => $data['email']];
                unset($data['email']);
            }

            // Phone number change request
            if (isset($data['phone_number']) && $data['phone_number'] !== $psw->phone_number) {
                $this->otpService->resendOtp(
                    $data['phone_number'],
                    'phone_update',
                    get_class($psw),
                    $psw->id
                );
                $pendingChanges[] = ['type' => 'phone', 'value' => $data['phone_number']];
                unset($data['phone_number']);
            }

            if (!empty($pendingChanges)) {
                return $this->success([
                    'pending_verifications' => $pendingChanges
                ], 'Verification OTP sent for pending contact changes. Please verify to complete.');
            }

            // Update profile data (non-contact fields)
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

    public function verifyContactChange(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $type = $data['type']; // email or phone
            $newValue = $data['new_value'];
            $otpType = $type === 'email' ? 'email_update' : 'phone_update';

            // Verify OTP for the given identifier and type
            $this->otpService->verifyOtp(
                $newValue,
                $data['otp_code'],
                $otpType
            );

            // Update PSW main record
            $update = $type === 'email' ? ['email' => $newValue] : ['phone_number' => $newValue];
            $psw = $this->pswRepository->update($psw, $update);

            $pswWithProfile = $this->pswProfileRepository->getPswWithProfile($psw->id);

            return $this->success([
                'psw' => $pswWithProfile,
                'profile' => $pswWithProfile->profile
            ], ucfirst($type) . ' updated successfully');
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
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $deleted = $this->pswProfileRepository->delete($psw->id);

            if (!$deleted) {
                $this->fail('Profile not found', 404);
            }

            return $this->success(null, 'PSW profile deleted successfully');
        });
    }
}