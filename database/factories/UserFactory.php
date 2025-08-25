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
        return $this->state(fn() => [
            'name' => 'Julio',
            'email' => 'julio.oliveira@alpes.one',
            'password' => Hash::make('carbel123'),
        ]);
    }
}
