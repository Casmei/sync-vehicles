<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\SourceType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InMemoryVehicleRepository implements VehicleRepository
{
    private array $items = [];

    public function __construct(array $seed = [])
    {
        foreach ($seed as $row) {
            $id = $row['id'] ?? (string) Str::uuid();
            $v = new Vehicle($row);
            $v->id = $id;
            $this->items[$id] = $v;
        }
    }

    public function paginate(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $all = $this->all()->values();
        $total = $all->count();
        $start = max(0, ($page - 1) * $perPage);
        $slice = $all->slice($start, $perPage)->values();

        return new Paginator($slice, $total, $perPage, $page);
    }


    public function all(): Collection
    {
        return collect($this->items)->sortBy('brand')->values();
    }

    public function find(string $id): ?Vehicle
    {
        return $this->items[$id] ?? null;
    }

    public function create(array $data): Vehicle
    {
        $id = $data['id'] ?? (string) Str::uuid();
        $v = new Vehicle($data);
        $v->id = $id;
        $this->items[$id] = $v;
        return $v;
    }

    public function update(string $id, array $data): Vehicle
    {
        $v = $this->find($id);
        if (!$v)
            throw new \RuntimeException('Vehicle not found');
        foreach ($data as $k => $val)
            $v->{$k} = $val;
        $this->items[$id] = $v;
        return $v;
    }

    public function delete(string $id): void
    {
        if (!isset($this->items[$id]))
            throw new \RuntimeException('Vehicle not found');
        unset($this->items[$id]);
    }

    public function countExternal(): int
    {
        return $this->all()->where('source', SourceType::EXTERNAL)->count();
    }

    public function maxExternalUpdatedAt(): ?string
    {
        $max = $this->all()
            ->where('source', SourceType::EXTERNAL)
            ->max('external_updated_at');

        return $max ? (string) $max : null;
    }

    public function findByExternalId(string $externalId): ?Vehicle
    {
        $found = $this->all()
            ->first(fn(Vehicle $v) => $v->source === SourceType::EXTERNAL && $v->external_id === $externalId);
        return $found;
    }
}
