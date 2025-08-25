<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\SourceType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentVehicleRepository implements VehicleRepository
{
    public function paginate(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return Vehicle::query()
            ->orderByDesc('updated_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function all(): Collection
    {
        return Vehicle::query()->orderBy('brand')->get();
    }

    public function find(string $id): ?Vehicle
    {
        return Vehicle::find($id);
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(string $id, array $data): Vehicle
    {
        $v = Vehicle::findOrFail($id);
        $v->fill($data)->save();
        return $v;
    }

    public function delete(string $id): void
    {
        $v = Vehicle::findOrFail($id);
        $v->delete();
    }

    public function countExternal(): int
    {
        return (int) Vehicle::query()
            ->where('source', SourceType::EXTERNAL)
            ->count();
    }

    public function maxExternalUpdatedAt(): ?string
    {
        $max = Vehicle::query()
            ->where('source', SourceType::EXTERNAL)
            ->max('external_updated_at');

        return $max ? (string) $max : null;
    }

    public function findByExternalId(string $externalId): ?Vehicle
    {
        return Vehicle::query()
            ->where('source', SourceType::EXTERNAL)
            ->where('external_id', $externalId)
            ->first();
    }
}
