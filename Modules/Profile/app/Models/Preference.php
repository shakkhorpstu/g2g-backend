<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    protected $table = 'preferences';

    protected $fillable = [
        'title',
    ];
}
