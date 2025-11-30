<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilePreference extends Model
{
    protected $table = 'profile_preferences';

    protected $fillable = [
        'preference_id',
        'owner_id',
        'owner_type',
    ];

    /**
     * The owning model (user profile or psw profile).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function preference(): BelongsTo
    {
        return $this->belongsTo(Preference::class);
    }
}
