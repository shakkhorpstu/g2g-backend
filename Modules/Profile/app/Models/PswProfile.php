<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PswProfile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psw_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'psw_id',
        'available_status',
        'hourly_rate',
        'include_driving_allowance',
        'driving_allowance_per_km',
        'has_own_vehicle',
        'has_wheelchair_accessible_vehicle',
    ];

    protected $casts = [
        'available_status' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'include_driving_allowance' => 'boolean',
        'driving_allowance_per_km' => 'decimal:2',
        'has_own_vehicle' => 'boolean',
        'has_wheelchair_accessible_vehicle' => 'boolean',
    ];

    /**
     * Get the PSW that owns the profile.
     */
    public function psw(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Psw::class);
    }
}
