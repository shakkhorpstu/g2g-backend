<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;

class PswServiceCategory extends Model
{
    protected $table = 'psw_service_categories';

    protected $fillable = [
        'psw_profile_id',
        'psw_id',
        'service_category_id',
    ];

    protected $casts = [
        'psw_profile_id' => 'integer',
        'psw_id' => 'integer',
        'service_category_id' => 'integer',
    ];
}
