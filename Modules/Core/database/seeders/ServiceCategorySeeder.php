<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\ServiceCategory;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'title' => 'Basic Ride',
                'subtitle' => 'Provide transport using their own car',
                'price' => 9.99,
                'base_fare' => 40.00, // According to the image
                'ride_charge' => 1.00, // $1/km
                'time_charge' => 0.50, // $0.50/min
                'platform_fee' => 0.15, // 15% of total (to be handled as a percentage in logic)
                'platform_fee_type' => 'percent', // optional, implement if needed in your system
            ],
            [
                'title' => 'Assisted Ride',
                'subtitle' => 'Accompany the client while using an Uber/WAV',
                'price' => 29.99,
                'base_fare' => 35.00, // According to the image
                'ride_charge' => 1.00, // $1/km
                'time_charge' => 0.50, // $0.50/min
                'platform_fee' => 0.20, // 20% of total
                'platform_fee_type' => 'percent',
            ],
            [
                'title' => 'Airport Escort',
                'subtitle' => 'Assist with check-in, gate escort, and paperwork',
                'price' => 39.99,
                'base_fare' => 200.00, // According to the image
                'ride_charge' => 0.00, // Not specified, assuming transport is through Uber (handled externally)
                'time_charge' => 0.50, // $0.50/min
                'platform_fee' => 0.20, // 20% of total
                'platform_fee_type' => 'percent',
            ],
            [
                'title' => 'Full Travel Assistance',
                'subtitle' => 'Travel with the client to their destination and assist throughout the trip',
                'price' => 39.99,
                'base_fare' => 850.00, // According to the image
                'ride_charge' => 0.00, // Not specified
                'time_charge' => 0.50, // $0.50/min
                'platform_fee' => 0.20, // 20% of total
                'platform_fee_type' => 'percent',
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::create($category);
        }
    }
}
