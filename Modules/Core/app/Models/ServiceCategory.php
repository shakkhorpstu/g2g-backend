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
        'price' => 'double',
        'base_fare' => 'double',
        'ride_charge' => 'double',
        'time_charge' => 'double',
        'platform_fee' => 'double',
    ];
}