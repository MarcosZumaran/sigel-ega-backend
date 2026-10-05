<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AsignacionDocenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsignacionDocenteController extends Controller
{
    public function __construct(private readonly AsignacionDocenteService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'docente_id' => 'nullable|integer|exists:docentes,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
        ]);

        return response()->json($this->service->getAll($filters));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'docente_id' => 'required|integer|exists:docentes,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'area_id' => 'nullable|integer|exists:areas,id',
            'es_encargado' => 'nullable|boolean',
        ]);

        $model = $this->service->create($data);

        return response()->json($model->load(['docente', 'seccion', 'area']), 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'docente_id' => 'sometimes|integer|exists:docentes,id',
            'seccion_id' => 'sometimes|integer|exists:secciones,id',
            'area_id' => 'nullable|integer|exists:areas,id',
            'es_encargado' => 'nullable|boolean',
        ]);

        $model = $this->service->update($id, $data);

        return response()->json($model->load(['docente', 'seccion', 'area']));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Asignación eliminada.']);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }
}
