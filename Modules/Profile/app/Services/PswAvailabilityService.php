<?php

namespace Modules\Profile\Services;

use App\Shared\Services\BaseService;
use Modules\Profile\Contracts\Repositories\PswAvailabilityRepositoryInterface;
use Modules\Profile\Contracts\Repositories\PswProfileRepositoryInterface;

class PswAvailabilityService extends BaseService
{
    protected PswAvailabilityRepositoryInterface $repository;
    protected PswProfileRepositoryInterface $pswProfileRepository;

    public function __construct(PswAvailabilityRepositoryInterface $repository, PswProfileRepositoryInterface $pswProfileRepository)
    {
        $this->repository = $repository;
        $this->pswProfileRepository = $pswProfileRepository;
    }

    public function listForAuthenticatedPsw(): array
    {
        $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

        // ensure profile exists
        $profile = $this->pswProfileRepository->findByPswId($psw->id);

        if (! $profile) {
            // create initial profile if missing
            $this->pswProfileRepository->create($psw->id, []);
            $profile = $this->pswProfileRepository->findByPswId($psw->id);
        }

        $days = $this->repository->listDaysByProfileId($profile->id);
        $slots = $this->repository->listSlotsByProfileId($profile->id);

        return $this->success([
            'min_booking_slot' => $profile->min_booking_slot ?? 30,
            'days' => $days,
            // 'slots' => $slots,
        ], 'Availability retrieved successfully');
    }

    public function syncForAuthenticatedPsw(array $payload): array
    {
        return $this->executeWithTransaction(function () use ($payload) {
            $psw = $this->getAuthenticatedUserOrFail(['psw-api'], 'PSW not authenticated');

            $profile = $this->pswProfileRepository->findByPswId($psw->id);

            if (!$profile) {
                $this->pswProfileRepository->create($psw->id, []);
                $profile = $this->pswProfileRepository->findByPswId($psw->id);
            }

            $days = $payload['days'] ?? [];
            $minBookingSlot = $payload['min_booking_slot'] ?? null;

            $result = $this->repository->syncForPsw($psw->id, $profile->id, $days, $minBookingSlot);

            return $this->success($result, 'Availability synced successfully');
        });
    }
}
