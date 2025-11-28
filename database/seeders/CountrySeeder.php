<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'United States', 'code' => 'USA', 'phone_code' => '+1', 'is_active' => true],
            ['name' => 'Canada', 'code' => 'CAN', 'phone_code' => '+1', 'is_active' => true],
            ['name' => 'United Kingdom', 'code' => 'GBR', 'phone_code' => '+44', 'is_active' => true],
            ['name' => 'Australia', 'code' => 'AUS', 'phone_code' => '+61', 'is_active' => true],
            ['name' => 'Germany', 'code' => 'DEU', 'phone_code' => '+49', 'is_active' => true],
            ['name' => 'France', 'code' => 'FRA', 'phone_code' => '+33', 'is_active' => true],
            ['name' => 'India', 'code' => 'IND', 'phone_code' => '+91', 'is_active' => true],
            ['name' => 'China', 'code' => 'CHN', 'phone_code' => '+86', 'is_active' => true],
            ['name' => 'Japan', 'code' => 'JPN', 'phone_code' => '+81', 'is_active' => true],
            ['name' => 'Brazil', 'code' => 'BRA', 'phone_code' => '+55', 'is_active' => true],
            ['name' => 'Mexico', 'code' => 'MEX', 'phone_code' => '+52', 'is_active' => true],
            ['name' => 'Spain', 'code' => 'ESP', 'phone_code' => '+34', 'is_active' => true],
            ['name' => 'Italy', 'code' => 'ITA', 'phone_code' => '+39', 'is_active' => true],
            ['name' => 'Netherlands', 'code' => 'NLD', 'phone_code' => '+31', 'is_active' => true],
            ['name' => 'Switzerland', 'code' => 'CHE', 'phone_code' => '+41', 'is_active' => true],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}