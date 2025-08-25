<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\SourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class VehicleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cria um usuário e "loga" com Sanctum para todos os testes
        $user = User::factory()->create([
            'email' => 'tester@example.com',
            'password' => Hash::make('secret123'),
        ]);

        // Se você usa abilities/escopos, passe-os no 2º parâmetro (ex.: ['vehicles:read'])
        Sanctum::actingAs($user, ['*']);
    }

    /** LIST **/
    public function test_index_paginates_with_ListVehicleRequest(): void
    {
        Vehicle::factory()->create(['brand' => 'A']);
        Vehicle::factory()->create(['brand' => 'B']);
        Vehicle::factory()->create(['brand' => 'C']);

        $this->getJson('/api/vehicles?quantidade=2&pagina=1')
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $j) =>
                $j->where('current_page', 1)
                    ->where('per_page', 2)
                    ->where('total', 3)
                    ->has('data', 2)
                    ->etc()
            );
    }

    public function test_index_validation_error_when_invalid_query_params(): void
    {
        $this->getJson('/api/vehicles?quantidade=0&pagina=0')
            ->assertStatus(422);
    }

    /** STORE **/
    public function test_store_creates_local_and_returns_201_no_body(): void
    {
        $payload = [
            'type' => 'carro',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'version' => 'XEI',
            'year' => ['model' => 2025, 'build' => 2025],
            'price' => 125900,
        ];

        $this->postJson('/api/vehicles', $payload)
            ->assertNoContent(Response::HTTP_CREATED);

        $this->assertDatabaseCount('vehicles', 1);
        $this->assertDatabaseHas('vehicles', [
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'source' => SourceType::LOCAL, // controller força LOCAL
        ]);
    }

    public function test_store_ignores_client_source_external_and_still_creates_local(): void
    {
        $payload = [
            'type' => 'carro',
            'brand' => 'VW',
            'model' => 'Golf',
            'version' => 'Highline',
            'year' => ['model' => 2024, 'build' => 2024],
            'price' => 150000,
            'source' => 'external', // será ignorado/sobrescrito
        ];

        $this->postJson('/api/vehicles', $payload)
            ->assertNoContent(Response::HTTP_CREATED);

        $this->assertDatabaseCount('vehicles', 1);
        $this->assertDatabaseHas('vehicles', [
            'brand' => 'VW',
            'model' => 'Golf',
            'source' => SourceType::LOCAL,
        ]);
    }

    /** SHOW **/
    public function test_show_returns_vehicle(): void
    {
        $v = Vehicle::factory()->create([
            'brand' => 'Ford',
            'model' => 'Focus',
        ]);

        $this->getJson("/api/vehicles/{$v->id}")
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $j) =>
                $j->where('id', $v->id)
                    ->where('brand', 'Ford')
                    ->where('model', 'Focus')
                    ->etc()
            );
    }

    public function test_show_404_when_not_found(): void
    {
        $this->getJson('/api/vehicles/zzz')->assertStatus(404);
    }

    /** UPDATE **/
    public function test_update_local_ok(): void
    {
        $v = Vehicle::factory()->create([
            'brand' => 'Honda',
            'model' => 'Civic',
            'color' => 'Prata',
            'source' => SourceType::LOCAL,
        ]);

        $this->putJson("/api/vehicles/{$v->id}", ['color' => 'Preto'])
            ->assertOk()
            ->assertJson(['color' => 'Preto']);

        $this->assertDatabaseHas('vehicles', [
            'id' => $v->id,
            'color' => 'Preto',
        ]);
    }

    public function test_update_external_forbidden_403(): void
    {
        $v = Vehicle::factory()->create([
            'brand' => 'Hyundai',
            'model' => 'Creta',
            'source' => SourceType::EXTERNAL,
        ]);

        $this->putJson("/api/vehicles/{$v->id}", ['color' => 'Branco'])
            ->assertStatus(403)
            ->assertJson(['message' => 'External vehicles are read-only.']);

        // garante que não alterou
        $this->assertDatabaseHas('vehicles', [
            'id' => $v->id,
            'source' => SourceType::EXTERNAL,
        ]);
    }

    /** DESTROY **/
    public function test_destroy_local_no_content_204(): void
    {
        $v = Vehicle::factory()->create([
            'brand' => 'Chevrolet',
            'model' => 'Onix',
            'source' => SourceType::LOCAL,
        ]);

        $this->deleteJson("/api/vehicles/{$v->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('vehicles', ['id' => $v->id]);
    }

    public function test_destroy_external_forbidden_403(): void
    {
        $v = Vehicle::factory()->create([
            'brand' => 'BMW',
            'model' => '320i',
            'source' => SourceType::EXTERNAL,
        ]);

        $this->deleteJson("/api/vehicles/{$v->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'External vehicles are read-only.']);

        $this->assertDatabaseHas('vehicles', ['id' => $v->id]);
    }
}
