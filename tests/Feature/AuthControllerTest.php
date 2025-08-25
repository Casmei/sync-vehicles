<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_user(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email']
            ]);
    }


    public function test_login_validation_errors(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'not-email',
            'password' => '123',
        ])->assertStatus(422);
    }
}
