<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Psw;

class PswSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding PSWs...');

        // Create test PSWs
        Psw::factory()->count(5)->create();

        // Create a test PSW with known credentials
        Psw::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'PSW',
            'email' => 'testpsw@example.com',
            'password' => bcrypt('password'),
            'role' => 'psw',
        ]);

        $this->command->info('PSWs seeded successfully!');
    }
}