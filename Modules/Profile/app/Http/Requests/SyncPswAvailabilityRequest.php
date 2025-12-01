<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPswAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_booking_slot' => 'sometimes|integer|in:15,30,45,60',
            'days' => 'sometimes|array',
            'days.*.day_of_week' => 'required_with:days|integer|between:0,6',
            'days.*.is_available' => 'required_with:days|boolean',
            'slots' => 'sometimes|array',
            'slots.*.id' => 'sometimes|integer|exists:psw_availability_slots,id',
            'slots.*.day_of_week' => 'required_with:slots|integer|between:0,6',
            'slots.*.start_time' => ['required_with:slots','regex:/^\d{1,2}:\d{2}$/'],
            'slots.*.end_time' => ['required_with:slots','regex:/^\d{1,2}:\d{2}$/'],
            'slots.*.slot_duration_minutes' => 'sometimes|integer|min:1',
            'slots.*.is_active' => 'sometimes|boolean',
        ];
    }

    public function getSanitized(): array
    {
        $min = $this->has('min_booking_slot') ? (int) $this->input('min_booking_slot') : null;

        // Build days array where each day may contain nested slots
        $daysInput = $this->input('days', []);
        $days = [];
        foreach ($daysInput as $d) {
            $dow = (int) ($d['day_of_week'] ?? 0);
            $isAvailable = (bool) ($d['is_available'] ?? false);

            $slots = [];
            foreach ($d['slots'] ?? [] as $s) {
                $start = isset($s['start_time']) ? preg_replace('/\s+/', '', $s['start_time']) : null;
                $end = isset($s['end_time']) ? preg_replace('/\s+/', '', $s['end_time']) : null;

                $slots[] = [
                    'id' => isset($s['id']) ? (int) $s['id'] : null,
                    'day_of_week' => $dow,
                    'start_time' => $start,
                    'end_time' => $end,
                    'slot_duration_minutes' => isset($s['slot_duration_minutes']) ? (int) $s['slot_duration_minutes'] : null,
                    'is_active' => array_key_exists('is_active', $s) ? (bool) $s['is_active'] : true,
                ];
            }

            $days[] = [
                'day_of_week' => $dow,
                'is_available' => $isAvailable,
                'slots' => $slots,
            ];
        }

        return [
            'min_booking_slot' => $min,
            'days' => $days,
        ];
    }
}
