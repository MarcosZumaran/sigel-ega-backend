<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PeriodoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function __construct(private readonly PeriodoService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'anio' => 'required|integer|min:2000|max:2100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'activo' => 'nullable|boolean',
        ]);

        $model = $this->service->create($data);

        return response()->json($model, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string|max:50',
            'anio' => 'sometimes|integer|min:2000|max:2100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'activo' => 'nullable|boolean',
        ]);

        $model = $this->service->update($id, $data);

        return response()->json($model);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }

    public function activar(int $id): JsonResponse
    {
        $periodo = $this->service->activar($id);

        return response()->json([
            'message' => "Periodo {$periodo->nombre} activado correctamente.",
            'periodo' => $periodo,
        ]);
    }

    public function promocion(int $id): JsonResponse
    {
        $resumen = $this->service->promocionar($id);

        return response()->json([
            'message' => "Promocion aplicada: {$resumen['promovidos']} promovidos, {$resumen['egresados']} en ultimo grado.",
            'promovidos' => $resumen['promovidos'],
            'egresados' => $resumen['egresados'],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Periodo eliminado correctamente']);
    }
}
