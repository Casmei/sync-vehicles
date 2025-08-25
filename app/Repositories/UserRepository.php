<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepository
{
    public function findByEmail(string $email): ?User;
}
