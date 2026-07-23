<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'username'  => $username,
            'name'      => fake()->name(),
            'email'     => fake()->unique()->safeEmail(),
            'password'  => static::$password ??= Hash::make('Password@123'),
            'role'      => 'school_admin',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
