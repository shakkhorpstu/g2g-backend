<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $table = 'service_categories';

    protected $fillable = [
        'title',
        'subtitle',
        'price',
        'base_fare',
        'ride_charge',
        'time_charge',
        'platform_fee',
    ];

    protected $casts = [
        'price' => 'float',
        'base_fare' => 'float',
        'ride_charge' => 'float',
        'time_charge' => 'float',
        'platform_fee' => 'float',
    ];
}