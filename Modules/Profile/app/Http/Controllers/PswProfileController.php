<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Http\Requests\VerifyEmailChangeRequest;
use Modules\Profile\Http\Requests\SetAvailabilityRequest;
use Modules\Profile\Http\Requests\SetRatesRequest;
use Modules\Profile\Http\Requests\SyncPreferencesRequest;
use Modules\Profile\Services\PswProfileService;

class PswProfileController extends ApiController
{
    protected PswProfileService $pswProfileService;

    public function __construct(PswProfileService $pswProfileService)
    {
        parent::__construct();
        $this->pswProfileService = $pswProfileService;
    }

    public function show(): JsonResponse
    {
        return $this->executeService(fn() => $this->pswProfileService->getProfile());
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        return $this->executeService(fn() => $this->pswProfileService->updateProfile($request->getSanitizedData()));
    }

    public function verifyContactChange(VerifyEmailChangeRequest $request): JsonResponse
    {
        return $this->executeService(fn() => $this->pswProfileService->verifyContactChange($request->getSanitizedData()));
    }

    public function setAvailability(SetAvailabilityRequest $request): JsonResponse
    {
        return $this->executeService(fn() => $this->pswProfileService->setAvailability($request->validated()), 'Availability updated');
    }

    public function setRates(SetRatesRequest $request): JsonResponse
    {
        return $this->executeService(fn() => $this->pswProfileService->setRates($request->validated()), 'Rates updated');
    }

    /**
     * Get list of preferences attached to authenticated PSW profile
     */
    public function preferences(): JsonResponse
    {
        return $this->executeService(fn() => $this->pswProfileService->listPreferences(), 'Preferences retrieved');
    }

    /**
     * Sync preferences for authenticated PSW profile (add/update/delete in one API)
     *
     * Request payload: { "preferences": [1,2,3] }
     */
    public function syncPreferences(SyncPreferencesRequest $request): JsonResponse
    {
        $data = $request->getSanitized();

        return $this->executeService(fn() => $this->pswProfileService->syncPreferences($data['preferences'] ?? []), 'Preferences synced');
    }
}
