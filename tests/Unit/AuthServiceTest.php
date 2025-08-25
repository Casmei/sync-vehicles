<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\InMemoryUserRepository;
use App\Services\AuthService;
use DomainException;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_login_ok(): void
    {
        $repo = new InMemoryUserRepository([
            new User([
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => Hash::make('secret123'),
            ]),
        ]);

        $svc = new AuthService($repo);

        $u = $svc->login('test@example.com', 'secret123');

        $this->assertEquals('test@example.com', $u->email);
    }

    public function test_login_invalid_password(): void
    {
        $this->expectException(DomainException::class);

        $repo = new InMemoryUserRepository([
            new User([
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => Hash::make('secret123'),
            ]),
        ]);
        $svc = new AuthService($repo);
        $svc->login('test@example.com', 'wrong');
    }

    public function test_login_user_not_found(): void
    {
        $this->expectException(DomainException::class);
        $svc = new AuthService(new InMemoryUserRepository());
        $svc->login('ghost@example.com', 'anything');
    }
}
