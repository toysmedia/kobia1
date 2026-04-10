<?php

namespace Database\Factories\Community;

use App\Models\Community\CommunityUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Community\CommunityUser>
 */
class CommunityUserFactory extends Factory
{
    protected $model = CommunityUser::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'avatar'            => null,
            'bio'               => fake()->sentence(),
            'location'          => fake()->city(),
            'website'           => null,
            'is_verified'       => true,
            'is_banned'         => false,
            'ban_reason'        => null,
            'reputation'        => 0,
            'remember_token'    => Str::random(10),
        ];
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_banned'  => true,
            'ban_reason' => 'Spam or abusive behaviour',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'is_verified'       => false,
        ]);
    }
}
