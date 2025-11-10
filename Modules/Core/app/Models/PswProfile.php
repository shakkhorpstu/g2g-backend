<?php

namespace Modules\Core\Models;

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
        'language_id',
    ];

    /**
     * Get the PSW that owns the profile.
     */
    public function psw(): BelongsTo
    {
        return $this->belongsTo(Psw::class);
    }
}