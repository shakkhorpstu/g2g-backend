<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '+1234567890',
                'address' => '123 Admin Street',
                'bio' => 'System Administrator',
                'email_verified_at' => now(),
            ]
        );

        // Create regular user
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
                'role' => 'user',
                'phone' => '+1234567891',
                'address' => '456 User Avenue',
                'bio' => 'Regular application user',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Users seeded successfully!');
    }
}