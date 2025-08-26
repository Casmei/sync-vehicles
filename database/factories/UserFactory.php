<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => Hash::make('carbel'),
            'remember_token' => Str::random(10),
        ];
    }

    public function testUser(): self
    {
        return $this->state(function () {
            $user = User::firstOrCreate(
                ['email' => 'julio.oliveira@alpes.one'],
                [
                    'name' => 'Julio',
                    'password' => Hash::make('carbel123'),
                ]
            );

            return $user->toArray();
        });
    }
}
