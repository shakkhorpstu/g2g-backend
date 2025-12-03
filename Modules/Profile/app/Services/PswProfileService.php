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
     * Send 2FA OTP to PSW's chosen contact (email or phone)
     *
     * @param array $data ['method' => 'email'|'phone']
     * @return array
     */
    public function sendTwoFactor(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $method = $data['method'] ?? null;
            if (!in_array($method, ['email', 'phone'])) {
                $this->fail('Invalid method. Must be "email" or "phone"', 422);
            }

            $identifier = $method === 'email' ? $psw->email : $psw->phone_number;
            if (!$identifier) {
                $this->fail('No contact information available for selected method', 422);
            }

            $result = $this->otpService->resendOtp(
                $identifier,
                'two_factor',
                get_class($psw),
                $psw->id
            );

            return $this->success($result['data'] ?? [], 'Two-factor OTP sent');
        });
    }

    /**
     * Verify 2FA OTP and enable two factor on PSW profile
     *
     * @param array $data ['method' => 'email'|'phone', 'otp_code' => '123456']
     * @return array
     */
    public function verifyTwoFactor(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $method = $data['method'] ?? null;
            $otpCode = $data['otp_code'] ?? null;

            if (!in_array($method, ['email', 'phone'])) {
                $this->fail('Invalid method. Must be "email" or "phone"', 422);
            }

            if (!$otpCode) {
                $this->fail('OTP code is required', 422);
            }

            $identifier = $method === 'email' ? $psw->email : $psw->phone_number;
            if (!$identifier) {
                $this->fail('No contact information available for selected method', 422);
            }

            $this->otpService->verifyOtp($identifier, $otpCode, 'two_factor');

            // Update psw profile
            $this->pswProfileRepository->update($psw->id, [
                '2fa_enabled' => true,
                '2fa_identifier_key' => $method,
            ]);

            $pswWithProfile = $this->pswProfileRepository->getPswWithProfile($psw->id);

            return $this->success($pswWithProfile, 'Two-factor authentication enabled');
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

    /**
     * Update PSW bio
     *
     * @param array $data
     * @return array
     */
    public function updateBio(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $profile = $this->pswProfileRepository->findByPswId($psw->id);
            if (! $profile) {
                $this->createInitialProfile($psw->id, []);
            }
            
            $this->pswProfileRepository->update($psw->id, [
                'bio' => $data['bio'] ?? null,
            ]);

            $pswWithProfile = $this->pswProfileRepository->getPswWithProfile($psw->id);

            return $this->success($pswWithProfile, 'Bio updated successfully');
        });
    }

    /**
     * List preferences attached to authenticated PSW profile
     *
     * @return array
     */
    public function listPreferences(): array
    {
        $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

        $profile = $this->pswProfileRepository->findByPswId($psw->id);
        if (! $profile) {
            $this->createInitialProfile($psw->id, []);
            $profile = $this->pswProfileRepository->findByPswId($psw->id);
        }

        $prefs = $profile->preferences()->with('preference')->get()->map(function ($pp) {
            return [
                'id' => $pp->preference->id ?? null,
                'title' => $pp->preference->title ?? null,
            ];
        })->filter(fn($p) => $p['id'] !== null)->values()->toArray();

        return $this->success($prefs, 'PSW preferences retrieved successfully');
    }

    /**
     * Attach a preference to authenticated PSW profile
     *
     * @param int $preferenceId
     * @return array
     */
    public function attachPreference(int $preferenceId): array
    {
        return $this->executeWithTransaction(function () use ($preferenceId) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $profile = $this->pswProfileRepository->findByPswId($psw->id);
            if (! $profile) {
                $this->createInitialProfile($psw->id, []);
                $profile = $this->pswProfileRepository->findByPswId($psw->id);
            }

            // prevent duplicates
            $existing = $profile->preferences()->where('preference_id', $preferenceId)->first();
            if ($existing) {
                return $this->success(null, 'Preference already attached');
            }

            $profile->preferences()->create(['preference_id' => $preferenceId]);

            $prefs = $profile->preferences()->with('preference')->get()->map(function ($pp) {
                return [
                    'id' => $pp->preference->id ?? null,
                    'title' => $pp->preference->title ?? null,
                ];
            })->filter(fn($p) => $p['id'] !== null)->values()->toArray();

            return $this->success($prefs, 'Preference attached successfully');
        });
    }

    /**
     * Detach a preference from authenticated PSW profile
     *
     * @param int $preferenceId
     * @return array
     */
    public function detachPreference(int $preferenceId): array
    {
        return $this->executeWithTransaction(function () use ($preferenceId) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $profile = $this->pswProfileRepository->findByPswId($psw->id);
            if (! $profile) {
                $this->fail('Profile not found', 404);
            }

            $deleted = $profile->preferences()->where('preference_id', $preferenceId)->delete();

            if (! $deleted) {
                $this->fail('Preference not attached', 404);
            }

            $prefs = $profile->preferences()->with('preference')->get()->map(function ($pp) {
                return [
                    'id' => $pp->preference->id ?? null,
                    'title' => $pp->preference->title ?? null,
                ];
            })->filter(fn($p) => $p['id'] !== null)->values()->toArray();

            return $this->success($prefs, 'Preference detached successfully');
        });
    }

    /**
     * Sync preferences for authenticated PSW profile.
     * Accepts an array of preference IDs; will attach missing ones and detach removed ones.
     *
     * @param array $preferenceIds
     * @return array
     */
    public function syncPreferences(array $preferenceIds): array
    {
        return $this->executeWithTransaction(function () use ($preferenceIds) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $profile = $this->pswProfileRepository->findByPswId($psw->id);
            if (! $profile) {
                $this->createInitialProfile($psw->id, []);
                $profile = $this->pswProfileRepository->findByPswId($psw->id);
            }

            // Normalize incoming ids
            $new = array_values(array_filter(array_map('intval', $preferenceIds)));

            // Existing attached preference ids
            $existing = $profile->preferences()->pluck('preference_id')->map(fn($v) => (int)$v)->toArray();

            $toAttach = array_values(array_diff($new, $existing));
            $toDetach = array_values(array_diff($existing, $new));

            // Attach new preferences
            foreach ($toAttach as $prefId) {
                // avoid duplicate safety
                $profile->preferences()->firstOrCreate(['preference_id' => $prefId]);
            }

            // Detach removed preferences
            if (!empty($toDetach)) {
                $profile->preferences()->whereIn('preference_id', $toDetach)->delete();
            }

            // Return updated list
            $prefs = $profile->preferences()->with('preference')->get()->map(function ($pp) {
                return [
                    'id' => $pp->preference->id ?? null,
                    'title' => $pp->preference->title ?? null,
                ];
            })->filter(fn($p) => $p['id'] !== null)->values()->toArray();

            return $this->success($prefs, 'Preferences synced successfully');
        });
    }
}