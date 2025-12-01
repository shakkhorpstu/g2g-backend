<?php

namespace Modules\Profile\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Modules\Profile\Contracts\Repositories\PswAvailabilityRepositoryInterface;

class PswAvailabilityRepository implements PswAvailabilityRepositoryInterface
{
    protected string $daysTable = 'psw_availability_days';
    protected string $slotsTable = 'psw_availability_slots';
    protected string $profilesTable = 'psw_profiles';

    public function listDaysByProfileId(int $pswProfileId): array
    {
        $rows = DB::table($this->daysTable)
            ->where('psw_profile_id', $pswProfileId)
            ->orderBy('day_of_week')
            ->get();

        $days = $rows->map(function ($r) use ($pswProfileId) {
            $day = (array) $r;

            $slots = DB::table($this->slotsTable)
                ->where('psw_profile_id', $pswProfileId)
                ->where('availability_day_id', $r->id)
                ->orderBy('start_time')
                ->get()
                ->map(function ($s) {
                    $start = $s->start_time;
                    $end = $s->end_time;
                    try {
                        $startFormatted = Carbon::createFromFormat('H:i:s', $start)->format('g:i A');
                    } catch (\Throwable $e) {
                        $startFormatted = $start;
                    }
                    try {
                        $endFormatted = Carbon::createFromFormat('H:i:s', $end)->format('g:i A');
                    } catch (\Throwable $e) {
                        $endFormatted = $end;
                    }

                    $arr = (array) $s;
                    $arr['start_time_formatted'] = $startFormatted;
                    $arr['end_time_formatted'] = $endFormatted;

                    return $arr;
                })->toArray();

            $day['slots'] = $slots;

            return $day;
        })->toArray();

        return $days;
    }

    public function listSlotsByProfileId(int $pswProfileId): array
    {
        $rows = DB::table($this->slotsTable)
            ->where('psw_profile_id', $pswProfileId)
            ->orderBy('availability_day_id')
            ->orderBy('start_time')
            ->get();

        return $rows->map(function ($r) {
            $start = $r->start_time;
            $end = $r->end_time;
            try {
                $startFormatted = Carbon::createFromFormat('H:i:s', $start)->format('g:i A');
            } catch (\Throwable $e) {
                $startFormatted = $start;
            }
            try {
                $endFormatted = Carbon::createFromFormat('H:i:s', $end)->format('g:i A');
            } catch (\Throwable $e) {
                $endFormatted = $end;
            }

            $arr = (array) $r;
            $arr['start_time_formatted'] = $startFormatted;
            $arr['end_time_formatted'] = $endFormatted;

            return $arr;
        })->toArray();
    }

    public function syncForPsw(int $pswId, int $pswProfileId, array $daysPayload, ?int $minBookingSlot): array
    {
        return DB::transaction(function () use ($pswId, $pswProfileId, $daysPayload, $minBookingSlot) {
            $now = Carbon::now();

            // Update min_booking_slot on psw_profiles if provided
            if (!is_null($minBookingSlot)) {
                DB::table($this->profilesTable)
                    ->where('id', $pswProfileId)
                    ->update(['min_booking_slot' => $minBookingSlot, 'updated_at' => $now]);
            }

            // Normalize days payload and upsert; expect each day may include 'slots'
            foreach ($daysPayload as $d) {
                $dow = (int) $d['day_of_week'];
                $isAvailable = (bool) ($d['is_available'] ?? false);
                DB::table($this->daysTable)->updateOrInsert(
                    ['psw_profile_id' => $psw_profileId ?? $pswProfileId, 'day_of_week' => $dow],
                    ['is_available' => $isAvailable, 'updated_at' => $now, 'created_at' => $now]
                );
            }

            // Fetch day IDs mapping
            $dayRows = DB::table($this->daysTable)->where('psw_profile_id', $pswProfileId)->get();
            $dayIdMap = [];
            foreach ($dayRows as $r) {
                $dayIdMap[(int) $r->day_of_week] = (int) $r->id;
            }

            // Prepare existing slots map (id => slot)
            $existingSlots = DB::table($this->slotsTable)
                ->where('psw_profile_id', $pswProfileId)
                ->get()
                ->keyBy('id')
                ->toArray();

            $incomingIds = [];

            // Process slots nested inside days
            foreach ($daysPayload as $d) {
                $dow = (int) $d['day_of_week'];
                $slots = $d['slots'] ?? [];

                if (!isset($dayIdMap[$dow])) {
                    throw ValidationException::withMessages(['days' => ["Day {$dow} must exist or be included in days payload."]]);
                }

                $availabilityDayId = $dayIdMap[$dow];

                foreach ($slots as $slot) {
                    $slotId = isset($slot['id']) ? (int) $slot['id'] : null;

                    // Parse times into H:i:s
                    $start = Carbon::createFromFormat('H:i', $slot['start_time'])->format('H:i:s');
                    $end = Carbon::createFromFormat('H:i', $slot['end_time'])->format('H:i:s');

                    if ($end <= $start) {
                        throw ValidationException::withMessages(['slots' => ["End time must be after start time for day {$dow}."]]);
                    }

                    // Check duplicate exact times
                    $duplicate = DB::table($this->slotsTable)
                        ->where('psw_profile_id', $pswProfileId)
                        ->where('availability_day_id', $availabilityDayId)
                        ->where('start_time', $start)
                        ->where('end_time', $end);

                    if ($slotId) {
                        $duplicate->where('id', '!=', $slotId);
                    }

                    if ($duplicate->exists()) {
                        throw ValidationException::withMessages(['slots' => ["Duplicate slot time for day {$dow} detected."]]);
                    }

                    // Check overlaps: any existing slot where start < new_end AND end > new_start
                    $overlap = DB::table($this->slotsTable)
                        ->where('psw_profile_id', $pswProfileId)
                        ->where('availability_day_id', $availabilityDayId)
                        ->whereRaw('start_time < ? AND end_time > ?', [$end, $start]);

                    if ($slotId) {
                        $overlap->where('id', '!=', $slotId);
                    }

                    if ($overlap->exists()) {
                        throw ValidationException::withMessages(['slots' => ["Overlapping slot for day {$dow} detected."]]);
                    }

                    $data = [
                        'psw_profile_id' => $pswProfileId,
                        'availability_day_id' => $availabilityDayId,
                        'start_time' => $start,
                        'end_time' => $end,
                        'slot_duration_minutes' => isset($slot['slot_duration_minutes']) ? (int) $slot['slot_duration_minutes'] : 30,
                        'is_active' => isset($slot['is_active']) ? (bool) $slot['is_active'] : true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ];

                    if ($slotId) {
                        DB::table($this->slotsTable)->where('id', $slotId)->update($data);
                        $incomingIds[] = $slotId;
                    } else {
                        $incomingIds[] = DB::table($this->slotsTable)->insertGetId($data);
                    }
                }
            }

            // Delete slots not in incoming payload
            $existingIds = array_map('intval', array_keys((array) $existingSlots));
            $toDelete = array_values(array_diff($existingIds, $incomingIds));
            if (!empty($toDelete)) {
                DB::table($this->slotsTable)->whereIn('id', $toDelete)->delete();
            }

            return [
                'days' => $this->listDaysByProfileId($pswProfileId),
                'min_booking_slot' => DB::table($this->profilesTable)->where('id', $pswProfileId)->value('min_booking_slot'),
            ];
        });
    }
}
