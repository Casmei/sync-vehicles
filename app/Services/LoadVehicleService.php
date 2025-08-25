<?php

namespace App\Services;

use App\Repositories\VehicleRepository;
use App\SourceType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LoadVehicleService
{
    public const JSON_PATH = 'exports/vehicles.json';

    public function __construct(private readonly VehicleRepository $repo)
    {
    }

    public function fetchExternalVehicles(string $url): ?array
    {
        try {
            $res = Http::timeout(60)->get($url);
            if ($res->failed()) {
                return null;
            }
            $json = $res->json();
            return is_array($json) ? $json : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function computeExternalSignature(array $external): array
    {
        $count = count($external);
        $maxUpdated = null;

        foreach ($external as $row) {
            $u = $row['updated'] ?? $row['updated_at'];
            if (!$u)
                continue;
            $ts = strtotime((string) $u);
            if ($ts !== false) {
                $iso = gmdate('c', $ts);
                if (!$maxUpdated || $iso > $maxUpdated) {
                    $maxUpdated = $iso;
                }
            }
        }

        return [
            'count' => $count,
            'max_updated' => $maxUpdated ?? 'null',
        ];
    }

    public function computeLocalSignature(): array
    {
        $count = $this->repo->countExternal();
        $max = $this->repo->maxExternalUpdatedAt();

        return [
            'count' => $count,
            'max_updated' => $max ? (string) $max : 'null',
        ];
    }

    public function signaturesEqual(array $a, array $b): bool
    {
        if ($a['count'] !== $b['count']) {
            return false;
        }

        $aTs = $this->toTs($a['max_updated'] ?? null);
        $bTs = $this->toTs($b['max_updated'] ?? null);

        return $aTs === $bTs;
    }

    public function persistJsonStable(array $data): void
    {
        Storage::makeDirectory(dirname(self::JSON_PATH));
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        Storage::put(self::JSON_PATH, $json);
    }

    public function persistJsonVersioned(array $data): string
    {
        $ts = now()->format('Ymd_His');
        $path = "exports/vehicles_{$ts}.json";
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        Storage::put($path, $json);
        return $path;
    }

    public function syncExternal(array $external, ?callable $onTick = null): array
    {
        $created = $updated = $skipped = 0;

        DB::transaction(function () use ($external, &$created, &$updated, &$skipped, $onTick) {
            foreach ($external as $row) {
                $extId = (string) $row['id'];
                $incomingUpdated = Carbon::parse($row['updated'])->utc();

                $existing = $this->repo->findByExternalId($extId);
                $payload = $this->mountPayload($row, $extId, $incomingUpdated);

                if (!$existing) {
                    $this->repo->create(array_merge($payload, [
                        'id' => (string) Str::uuid(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));

                    $created++;
                    continue;
                }

                $currentUpdated = Carbon::parse($existing->external_updated_at)->utc();

                if ($incomingUpdated->utc()->gt($currentUpdated)) {
                    $this->repo->update($existing->id, array_merge($payload, [
                        'updated_at' => $incomingUpdated->utc(),
                    ]));
                    $updated++;
                } else {
                    $skipped++;
                }

                if ($onTick) {
                    $onTick();
                }
            }
        });

        return compact('created', 'updated', 'skipped');
    }

    private function mountPayload(array $row, $extId, $incomingUpdated): array
    {
        $payload = [
            'source' => SourceType::EXTERNAL,
            'external_id' => $extId,
            'external_updated_at' => $incomingUpdated->toDateTimeString(),
            'type' => $row['type'],
            'brand' => $row['brand'],
            'model' => $row['model'],
            'version' => $row['version'],
            'year' => $row['year'],
            'optionals_json' => $row['optionals'],
            'fotos_json' => $row['fotos'],
            'doors' => (int) $row['doors'],
            'board' => $row['board'],
            'chassi' => $row['chassi'],
            'transmission' => $row['transmission'],
            'km' => (int) $row['km'],
            'description' => $row['description'],
            'sold' => !!$row['sold'],
            'category' => $row['category'],
            'url_car' => $row['url_car'],
            'old_price' => $row['old_price'],
            'color' => $row['color'],
            'fuel' => $row['fuel'],
            'price' => $row['price'],
        ];

        return $payload;
    }

    private function toTs(?string $v): ?int
    {
        if (!$v)
            return null;
        $ts = strtotime($v);
        return $ts === false ? null : $ts;
    }
}
