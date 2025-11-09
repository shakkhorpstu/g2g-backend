<?php

namespace Database\Seeders;

use Modules\Core\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Core\Database\Seeders\CoreDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run Core module seeders
        $this->call([
            CoreDatabaseSeeder::class,
        ]);

        // Uncomment to create additional test users
        // User::factory(10)->create();

        // Create a test user if needed for development
        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);
    }
}
