<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Str;

class InMemoryUserRepository implements UserRepository
{
    private array $items = [];

    public function __construct(array $seed = [])
    {
        foreach ($seed as $u) {
            $this->addModel($u instanceof User ? $u : new User($u));
        }
    }

    private function addModel(User $u): void
    {
        if (!$u->id)
            $u->id = (string) Str::uuid();
        $this->items[$u->id] = $u;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->items as $u) {
            if ($u->email === $email)
                return $u;
        }
        return null;
    }

}
