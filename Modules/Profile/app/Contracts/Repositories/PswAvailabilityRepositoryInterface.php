<?php

namespace Modules\Profile\Contracts\Repositories;

interface PswAvailabilityRepositoryInterface
{
    public function listDaysByProfileId(int $pswProfileId): array;

    public function listSlotsByProfileId(int $pswProfileId): array;

    public function syncForPsw(int $pswId, int $pswProfileId, array $daysPayload, ?int $minBookingSlot): array;
}
