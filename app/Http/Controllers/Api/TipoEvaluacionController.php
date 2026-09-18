<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TipoEvaluacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoEvaluacionController extends Controller
{
    public function __construct(private readonly TipoEvaluacionService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos_evaluacion,nombre',
            'descripcion' => 'nullable|string|max:255',
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
            'nombre' => 'sometimes|string|max:100|unique:tipos_evaluacion,nombre,$id,id',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $model = $this->service->update($id, $data);

        return response()->json($model);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Tipo de Evaluación eliminado correctamente']);
    }
}
