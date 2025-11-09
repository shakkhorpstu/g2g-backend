<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\Psw;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Core\Models\Psw>
 */
class PswFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Psw::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password
            'phone_number' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['1', '2', '3']),
            'role' => 'psw',
            'address' => fake()->address(),
            'bio' => fake()->paragraph(2),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ];
    }


}