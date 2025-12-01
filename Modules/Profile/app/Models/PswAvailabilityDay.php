<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PswAvailabilityDay extends Model
{
    protected $table = 'psw_availability_days';

    protected $fillable = ['psw_profile_id', 'day_of_week', 'is_available'];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_available' => 'boolean',
    ];

    public function pswProfile(): BelongsTo
    {
        return $this->belongsTo(PswProfile::class, 'psw_profile_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(PswAvailabilitySlot::class, 'availability_day_id');
    }
}
