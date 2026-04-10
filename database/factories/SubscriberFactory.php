<?php

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscriber>
 */
class SubscriberFactory extends Factory
{
    protected $model = Subscriber::class;

    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'name'            => fake()->name(),
            'email'           => fake()->unique()->safeEmail(),
            'phone'           => fake()->numerify('07########'),
            'username'        => $username,
            'password_hash'   => Hash::make('password'),
            'radius_password' => Str::random(12),
            'connection_type' => 'pppoe',
            'status'          => 'active',
            'expires_at'      => now()->addDays(30),
            'created_by'      => 'admin',
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => 'expired',
            'expires_at' => now()->subDays(5),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
