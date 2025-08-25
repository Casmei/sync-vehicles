<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use App\SourceType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use DomainException;

class VehicleService
{
    public function __construct(private readonly VehicleRepository $repo)
    {
    }

    public function list(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $page);
    }

    public function show(string $id): Vehicle
    {
        $v = $this->repo->find($id);
        if (!$v)
            throw new DomainException('Vehicle not found');
        return $v;
    }

    public function create(array $data): Vehicle
    {
        if ($data['source'] !== SourceType::LOCAL) {
            throw new DomainException('Only local vehicles can be created via API.');
        }
        return $this->repo->create($data);
    }

    public function update(string $id, array $data): Vehicle
    {
        $current = $this->show($id);

        if ($current->source !== SourceType::LOCAL) {
            throw new DomainException('External vehicles are read-only.');
        }
        return $this->repo->update($id, $data);
    }

    public function delete(string $id): void
    {
        $current = $this->show($id);
        if ($current->source !== SourceType::LOCAL) {
            throw new DomainException('External vehicles are read-only.');
        }
        $this->repo->delete($id);
    }
}
