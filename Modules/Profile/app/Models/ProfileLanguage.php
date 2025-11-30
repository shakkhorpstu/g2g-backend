<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProfileLanguage extends Model
{
    protected $fillable = [
        'languageable_type',
        'languageable_id',
        'language',
    ];

    /**
     * Get the parent languageable model (User or PSW)
     */
    public function languageable(): MorphTo
    {
        return $this->morphTo();
    }
}
