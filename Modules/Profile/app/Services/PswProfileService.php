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

        return $this->success($pswWithProfile, 'PSW profile retrieved successfully');
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

            // If any pending changes require verification, return early
            if (!empty($pendingChanges)) {
                return $this->success([
                    'pending_verifications' => $pendingChanges
                ], 'Verification OTP sent for pending contact changes. Please verify to complete.');
            }

            // Update remaining profile data
            if (!empty($data)) {
                $this->pswProfileRepository->updateOrCreate($psw->id, $data);
            }

            // Get updated PSW with profile
            $pswWithProfile = $this->pswProfileRepository->getPswWithProfile($psw->id);

            return $this->success($pswWithProfile, 'PSW profile updated successfully');
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

            return $this->success($pswWithProfile, ucfirst($type) . ' updated successfully');
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

    /**
     * Set availability status for authenticated PSW
     *
     * @param array $data
     * @return array
     */
    public function setAvailability(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $profile = $this->pswProfileRepository->findByPswId($psw->id);
            if (! $profile) {
                $this->createInitialProfile($psw->id, []);
            }

            $this->pswProfileRepository->update($psw->id, [
                'available_status' => (bool) ($data['available_status'] ?? false),
            ]);

            $pswWithProfile = $this->pswProfileRepository->getPswWithProfile($psw->id);

            return $this->success($pswWithProfile, 'Availability updated successfully');
        });
    }

    /**
     * Set hourly rate and driving allowance settings for PSW
     *
     * @param array $data
     * @return array
     */
    public function setRates(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $profile = $this->pswProfileRepository->findByPswId($psw->id);
            if (! $profile) {
                $this->createInitialProfile($psw->id, []);
            }

            $update = [];
            if (array_key_exists('hourly_rate', $data)) {
                $update['hourly_rate'] = $data['hourly_rate'] === null ? null : (float) $data['hourly_rate'];
            }

            $include = (bool) ($data['include_driving_allowance'] ?? false);
            $update['include_driving_allowance'] = $include;

            if ($include && array_key_exists('driving_allowance_per_km', $data)) {
                $update['driving_allowance_per_km'] = $data['driving_allowance_per_km'] === null ? null : (float) $data['driving_allowance_per_km'];
            } else {
                $update['driving_allowance_per_km'] = null;
            }

            $this->pswProfileRepository->update($psw->id, $update);

            $pswWithProfile = $this->pswProfileRepository->getPswWithProfile($psw->id);

            return $this->success($pswWithProfile, 'Rates updated successfully');
        });
    }
}