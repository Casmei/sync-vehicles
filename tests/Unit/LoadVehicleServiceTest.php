<?php

namespace Tests\Unit;

use App\Repositories\InMemoryVehicleRepository;
use App\Services\LoadVehicleService;
use App\SourceType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoadVehicleServiceTest extends TestCase
{
    private function makeService(array $seed = []): array
    {
        $repo = new InMemoryVehicleRepository($seed);
        $svc = new LoadVehicleService($repo);

        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        return [$svc, $repo];
    }

    /**
     * Helper para simular item externo (payload de API).
     */
    private function extVehicle(array $overrides = []): array
    {
        $base = [
            'id' => 'eX',
            'brand' => 'MarcaExt',
            'model' => 'ModeloExt',
            'version' => '1.0',
            'year' => ['model' => 2024, 'build' => 2024],
            'color' => 'black',
            'fuel' => 'Gasoline',
            'doors' => 4,
            'km' => 0,
            'price' => 100000,
            'description' => 'ok',
            'created' => '2024-01-01T00:00:00Z',
            'updated' => '2024-01-02T00:00:00Z',
            'type' => 'carro',
            'optionals' => [],
            'fotos' => [],
            'board' => null,
            'chassi' => null,
            'transmission' => null,
            'sold' => null,
            'category' => null,
            'url_car' => null,
            'old_price' => null
        ];

        return array_replace_recursive($base, $overrides);
    }

    /**
     * Helper para seed local (repo).
     */
    private function localVehicle(array $overrides = []): array
    {
        $base = [
            'id' => 'vX',
            'source' => SourceType::EXTERNAL,
            'external_id' => 'eX',
            'brand' => 'Marca',
            'model' => 'Modelo',
            'version' => '1.0',
            'year' => ['model' => 2024, 'build' => 2024],
            'color' => 'black',
            'fuel' => 'Gasoline',
            'doors' => 4,
            'km' => 0,
            'price' => 100000,
            'description' => 'desc',
            'external_updated_at' => '2024-01-02 00:00:00',
            'updated_at' => '2024-01-02 00:00:00',
        ];

        return array_replace_recursive($base, $overrides);
    }

    public function test_fetchExternalVehicles_returns_array()
    {
        [$svc] = $this->makeService();

        Http::fake([
            'external.test/api*' => Http::response([
                $this->extVehicle([
                    'id' => 'e1',
                    'brand' => 'A',
                    'model' => 'M',
                    'year' => ['model' => 2020, 'build' => 2020],
                    'created' => '2024-01-01T00:00:00Z',
                    'updated' => '2024-01-02T00:00:00Z',
                ]),
            ], 200),
        ]);

        $data = $svc->fetchExternalVehicles('https://external.test/api/vehicles');
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('e1', $data[0]['id']);
        $this->assertIsArray($data[0]['year']);
        $this->assertEquals(2020, $data[0]['year']['model']);
        $this->assertEquals(2020, $data[0]['year']['build']);
    }

    public function test_compute_signatures_and_equal()
    {
        [$svc] = $this->makeService();

        $ext = [
            ['id' => 'e1', 'updated' => '2024-01-02T00:00:00Z'],
            ['id' => 'e2', 'updated' => '2024-01-03T00:00:00Z'],
        ];

        $seed = [
            $this->localVehicle([
                'id' => 'v1',
                'external_id' => 'e1',
                'external_updated_at' => '2024-01-02 00:00:00',
                'updated_at' => '2024-01-02 00:00:00',
            ]),
            $this->localVehicle([
                'id' => 'v2',
                'external_id' => 'e2',
                'external_updated_at' => '2024-01-03 00:00:00',
                'updated_at' => '2024-01-03 00:00:00',
            ]),
        ];

        [$svc2, $repo] = $this->makeService($seed);

        $extSig = $svc->computeExternalSignature($ext);
        $locSig = $svc2->computeLocalSignature();

        $this->assertSame($extSig['count'], count($repo->all()));
        $this->assertTrue($svc->signaturesEqual($extSig, $locSig));
    }

    public function test_syncExternal_creates_new_records()
    {
        [$svc, $repo] = $this->makeService();

        $incoming = [
            $this->extVehicle([
                'id' => 'e1',
                'brand' => 'Ford',
                'model' => 'Focus',
                'version' => '1.6',
                'year' => ['model' => 2019, 'build' => 2019],
                'color' => 'black',
                'fuel' => 'Gasoline',
                'doors' => 4,
                'km' => 10000,
                'price' => 90000,
                'description' => 'ok',
                'created' => '2024-01-01T00:00:00Z',
                'updated' => '2024-01-02T00:00:00Z',
            ]),
            $this->extVehicle([
                'id' => 'e2',
                'brand' => 'VW',
                'model' => 'Golf',
                'version' => '1.4',
                'year' => ['model' => 2020, 'build' => 2020],
                'color' => 'white',
                'fuel' => 'Gasoline',
                'doors' => 4,
                'km' => 5000,
                'price' => 120000,
                'description' => 'ok',
                'created' => '2024-01-01T00:00:00Z',
                'updated' => '2024-01-04T00:00:00Z',
            ]),
        ];

        $stats = $svc->syncExternal($incoming);
        $this->assertSame(['created' => 2, 'updated' => 0, 'skipped' => 0], $stats);

        $this->assertEquals(2, $repo->countExternal());
        $this->assertEquals('2024-01-04 00:00:00', $repo->maxExternalUpdatedAt());
    }

    public function test_syncExternal_updates_when_incoming_is_newer()
    {
        $seed = [
            $this->localVehicle([
                'id' => 'v-e1',
                'external_id' => 'e1',
                'brand' => 'Ford',
                'model' => 'Focus',
                'version' => '1.6',
                'year' => ['model' => 2019, 'build' => 2019],
                'color' => 'black',
                'fuel' => 'Gasoline',
                'doors' => 4,
                'km' => 10000,
                'price' => 90000,
                'description' => 'old',
                'external_updated_at' => '2024-01-02 00:00:00',
                'updated_at' => '2024-01-02 00:00:00',
            ]),
        ];

        [$svc, $repo] = $this->makeService($seed);

        $incoming = [
            $this->extVehicle([
                'id' => 'e1',
                'brand' => 'Ford',
                'model' => 'Focus',
                'version' => '1.6',
                'year' => ['model' => 2019, 'build' => 2019],
                'color' => 'black',
                'fuel' => 'Gasoline',
                'doors' => 4,
                'km' => 10000,
                'price' => 95000,
                'description' => 'new',
                'created' => '2024-01-01T00:00:00Z',
                'updated' => '2024-01-05T00:00:00Z',
            ]),
        ];

        $stats = $svc->syncExternal($incoming);
        $this->assertSame(['created' => 0, 'updated' => 1, 'skipped' => 0], $stats);

        $v = $repo->findByExternalId('e1');

        $this->assertNotNull($v);
        $this->assertEquals('new', $v->description);
        $this->assertEquals('2024-01-05 00:00:00', (string) $v->external_updated_at);
        $this->assertEquals('2024-01-05 00:00:00', (string) $v->updated_at);
    }

    public function test_syncExternal_skips_when_incoming_is_older_or_equal()
    {
        $seed = [
            $this->localVehicle([
                'id' => 'v-e1',
                'external_id' => 'e1',
                'brand' => 'Ford',
                'model' => 'Focus',
                'year' => ['model' => 2019, 'build' => 2019],
                'description' => 'stay',
                'external_updated_at' => '2024-01-05 00:00:00',
                'updated_at' => '2024-01-05 00:00:00',
            ]),
        ];

        [$svc, $repo] = $this->makeService($seed);

        $incoming = [
            $this->extVehicle([
                'id' => 'e1',
                'brand' => 'Ford',
                'model' => 'Focus',
                'year' => ['model' => 2019, 'build' => 2019],
                'description' => 'incoming-older',
                'created' => '2024-01-01T00:00:00Z',
                'updated' => '2024-01-04T00:00:00Z', // older
            ]),
        ];

        $stats = $svc->syncExternal($incoming);
        $this->assertSame(['created' => 0, 'updated' => 0, 'skipped' => 1], $stats);

        $v = $repo->findByExternalId('e1');
        $this->assertEquals('stay', $v->description);
        $this->assertEquals('2024-01-05 00:00:00', (string) $v->external_updated_at);
    }

    public function test_persist_json_files()
    {
        [$svc] = $this->makeService();

        Storage::fake(); // finge o disco padrão

        $payload = [
            $this->extVehicle([
                'id' => 'e1',
                'brand' => 'A',
                'model' => 'M',
                'year' => ['model' => 2020, 'build' => 2020],
                'created' => '2024-01-01T00:00:00Z',
                'updated' => '2024-01-02T00:00:00Z',
            ]),
        ];

        $svc->persistJsonStable($payload);
        Storage::assertExists(LoadVehicleService::JSON_PATH);

        $versioned = $svc->persistJsonVersioned($payload);
        Storage::assertExists($versioned);
        $this->assertStringStartsWith('exports/vehicles_', $versioned);
        $this->assertStringEndsWith('.json', $versioned);
    }
}
