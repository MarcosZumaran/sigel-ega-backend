<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    public function __construct(private readonly CalificacionService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'matricula_id' => 'required|integer|exists:matriculas,id',
            'area_id' => 'required|integer|exists:areas,id',
            'tipo_evaluacion_id' => 'required|integer|exists:tipos_evaluacion,id',
            'nota' => 'nullable|numeric|between:0,20',
            'nivel_logro' => 'nullable|in:AD,A,B,C',
            'escala' => 'nullable|in:literal,vigesimal',
            'es_nota_c' => 'nullable|boolean',
            'motivo_nota_c' => 'nullable|string|max:255',
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
            'matricula_id' => 'sometimes|integer|exists:matriculas,id',
            'area_id' => 'sometimes|integer|exists:areas,id',
            'tipo_evaluacion_id' => 'sometimes|integer|exists:tipos_evaluacion,id',
            'nota' => 'nullable|numeric|between:0,20',
            'nivel_logro' => 'nullable|in:AD,A,B,C',
            'escala' => 'nullable|in:literal,vigesimal',
            'es_nota_c' => 'nullable|boolean',
            'motivo_nota_c' => 'nullable|string|max:255',
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

        return response()->json(['message' => 'Calificación eliminado correctamente']);
    }
}
