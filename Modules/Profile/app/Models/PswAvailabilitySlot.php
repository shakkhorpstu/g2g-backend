<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PswAvailabilitySlot extends Model
{
    protected $table = 'psw_availability_slots';

    protected $fillable = [
        'psw_profile_id',
        'availability_day_id',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'slot_duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function pswProfile(): BelongsTo
    {
        return $this->belongsTo(PswProfile::class, 'psw_profile_id');
    }

    public function availabilityDay(): BelongsTo
    {
        return $this->belongsTo(PswAvailabilityDay::class, 'availability_day_id');
    }

    // Return start time formatted with AM/PM
    public function getStartTimeFormattedAttribute()
    {
        return Carbon::createFromFormat('H:i:s', $this->start_time)->format('g:i A');
    }

    // Return end time formatted with AM/PM
    public function getEndTimeFormattedAttribute()
    {
        return Carbon::createFromFormat('H:i:s', $this->end_time)->format('g:i A');
    }
}
