<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface VehicleRepository
{
    public function paginate(int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function all(): Collection;

    public function find(string $id): ?Vehicle;

    public function create(array $data): Vehicle;

    public function update(string $id, array $data): Vehicle;

    public function delete(string $id): void;

    public function countExternal(): int;

    public function maxExternalUpdatedAt(): ?string;

    public function findByExternalId(string $externalId): ?Vehicle;
}
