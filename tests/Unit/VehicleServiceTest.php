<?php

namespace Tests\Unit;

use App\Repositories\InMemoryVehicleRepository;
use App\Services\VehicleService;
use App\SourceType;
use DomainException;
use Tests\TestCase;

class VehicleServiceTest extends TestCase
{
    private function makeService(array $seed = []): VehicleService
    {
        $repo = new InMemoryVehicleRepository($seed);
        return new VehicleService($repo);
    }

    /**
     * Helper para criar um array de veículo válido com valores padrão,
     * permitindo sobrescrever campos conforme necessário.
     */
    private function vehicle(array $overrides = []): array
    {
        $base = [
            'type' => 'carro',
            'brand' => 'Marca',
            'model' => 'Modelo',
            'version' => 'Versão',
            'year' => ['model' => 2025, 'build' => 2025],
            'optionals_json' => [],
            'fotos_json' => [],
            'doors' => 4,
            'board' => 'ABC1D23',
            'chassi' => '9BWZZZ377VT004251',
            'transmission' => 'Automática',
            'km' => 0,
            'price' => 100000.00,
            'old_price' => null,
            'color' => 'Preto',
            'fuel' => 'Gasolina',
            'sold' => false,
            'category' => 'Sedan',
            'url_car' => 'marca-modelo-2025-automatica',
            'description' => 'Veículo de teste',
        ];

        return array_replace_recursive($base, $overrides);
    }

    public function test_create_local_vehicle_ok()
    {
        $svc = $this->makeService();

        $v = $svc->create($this->vehicle([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => ['model' => 2020, 'build' => 2020],
            'source' => SourceType::LOCAL,
        ]));

        $this->assertEquals('Toyota', $v->brand);
        $this->assertEquals(SourceType::LOCAL, $v->source);
        $this->assertIsArray($v->year);
        $this->assertEquals(2020, $v->year['model'] ?? null);
        $this->assertEquals(2020, $v->year['build'] ?? null);
    }

    public function test_create_external_should_fail()
    {
        $this->expectException(DomainException::class);
        $svc = $this->makeService();

        $svc->create($this->vehicle([
            'brand' => 'Tesla',
            'model' => '3',
            'year' => ['model' => 2023, 'build' => 2023],
            'source' => 'external',
        ]));
    }

    public function test_update_local_ok()
    {
        $svc = $this->makeService([
            $this->vehicle([
                'id' => '1',
                'brand' => 'Ford',
                'model' => 'Focus',
                'year' => ['model' => 2019, 'build' => 2019],
                'source' => 'local',
            ]),
        ]);

        $v = $svc->update('1', ['color' => 'black']);
        $this->assertEquals('black', $v->color);
    }

    public function test_update_external_should_fail()
    {
        $svc = $this->makeService([
            $this->vehicle([
                'id' => '2',
                'brand' => 'VW',
                'model' => 'Golf',
                'year' => ['model' => 2020, 'build' => 2020],
                'source' => 'external',
            ]),
        ]);

        $this->expectException(DomainException::class);
        $svc->update('2', ['color' => 'red']);
    }

    public function test_delete_local_ok()
    {
        $svc = $this->makeService([
            $this->vehicle([
                'id' => '3',
                'brand' => 'Honda',
                'model' => 'Civic',
                'year' => ['model' => 2018, 'build' => 2018],
                'source' => 'local',
            ]),
        ]);

        $svc->delete('3');
        $this->assertTrue(true); // se não lançar exceção, passou
    }

    public function test_delete_external_should_fail()
    {
        $svc = $this->makeService([
            $this->vehicle([
                'id' => '4',
                'brand' => 'BMW',
                'model' => '320i',
                'year' => ['model' => 2021, 'build' => 2021],
                'source' => 'external',
            ]),
        ]);

        $this->expectException(DomainException::class);
        $svc->delete('4');
    }

    public function test_list_pagination_basics()
    {
        $svc = $this->makeService([
            $this->vehicle(['id' => 'a', 'brand' => 'A', 'model' => 'M', 'year' => ['model' => 2020, 'build' => 2020], 'source' => 'local']),
            $this->vehicle(['id' => 'b', 'brand' => 'B', 'model' => 'M', 'year' => ['model' => 2020, 'build' => 2020], 'source' => 'external']),
            $this->vehicle(['id' => 'c', 'brand' => 'C', 'model' => 'M', 'year' => ['model' => 2020, 'build' => 2020], 'source' => 'local']),
        ]);

        $page1 = $svc->list(2, 1);
        $page2 = $svc->list(2, 2);

        // count() = itens na página
        $this->assertEquals(2, $page1->count());
        $this->assertEquals(1, $page2->count());

        // total() = total de itens
        $this->assertEquals(3, $page1->total());
    }
}
