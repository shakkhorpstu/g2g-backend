<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Profile\Models\Preference;

class PreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $preferences = [
            'Non-Smoker',
            'Vegetarian',
            'Non-vegetarian',
            'Comfortable with Pets',
            'Can bring first-aid kits',
            'Lifting Capacity',
            'Certified to cut diabetic nails',
            'Can assist with home physiotherapy',
        ];

        foreach ($preferences as $title) {
            Preference::updateOrCreate(
                ['title' => $title],
                []
            );
        }
    }
}
