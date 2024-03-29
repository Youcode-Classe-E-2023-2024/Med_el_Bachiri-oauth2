<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->userName();
        return [
            'name' => $name,
            'email' => $name.'@gmail.com',
            'password' => Hash::make($name.'@gmail.com'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', 'User')->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
