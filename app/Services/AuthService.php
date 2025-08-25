<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use DomainException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function login(string $email, string $password): User
    {
        $user = $this->users->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new DomainException('Invalid credentials.');
        }

        return $user;
    }
}
