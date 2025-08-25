<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListVehicleRequest;
use App\Http\Resources\VehicleCollection;
use App\Services\VehicleService;
use App\Http\Requests\CreateVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\SourceType;
use Symfony\Component\HttpFoundation\Response;
use DomainException;
/**
 * @tags Veículos 🚗
 */
class VehicleController extends Controller
{
    public function __construct(private readonly VehicleService $service)
    {
    }

    /**
     * Listar
     *
     * Retorna uma lista paginada de veículos.
     */
    public function index(ListVehicleRequest $request)
    {
        $perPage = (int) ($request->input('quantidade', 15));
        $page = (int) ($request->input('pagina', 1));

        $paginator = $this->service->list($perPage, $page);

        return (new VehicleCollection($paginator))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Criar
     *
     * Criar um novo veículo
     */
    public function store(CreateVehicleRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['source'] = SourceType::LOCAL;

            $this->service->create($validated);

            return response()->noContent(Response::HTTP_CREATED);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    /**
     * Detalhar
     *
     * Retorna os dados de um veículo específico.
     */
    public function show(string $id)
    {
        try {
            $v = $this->service->show($id);
            return response()->json($v);
        } catch (DomainException $e) {
            return response()->json(['message' => 'Not found'], 404);
        }
    }

    /**
     * Atualizar
     *
     * Atualiza os dados de um veículo.
     */
    public function update(UpdateVehicleRequest $request, string $id)
    {
        try {
            $validated = $request->validated();
            $v = $this->service->update($id, $validated);
            return response()->json($v);
        } catch (DomainException $e) {
            $code = $e->getMessage() === 'External vehicles are read-only.' ? 403 : 404;
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }

    /**
     * Excluir
     *
     * Remove um veículo definitivamente.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return response()->noContent();
        } catch (DomainException $e) {
            $code = $e->getMessage() === 'External vehicles are read-only.' ? 403 : 404;
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }
}
