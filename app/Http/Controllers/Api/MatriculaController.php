<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MatriculaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatriculaController extends Controller
{
    public function __construct(private readonly MatriculaService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'estudiante_id' => 'required|integer|exists:estudiantes,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'periodo_id' => 'required|integer|exists:periodos,id',
            'tipo_matricula_id' => 'required|integer|exists:tipos_matricula,id',
            'fecha' => 'nullable|date',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'observaciones' => 'nullable|string',
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
            'estudiante_id' => 'sometimes|integer|exists:estudiantes,id',
            'seccion_id' => 'sometimes|integer|exists:secciones,id',
            'periodo_id' => 'sometimes|integer|exists:periodos,id',
            'tipo_matricula_id' => 'sometimes|integer|exists:tipos_matricula,id',
            'fecha' => 'nullable|date',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'observaciones' => 'nullable|string',
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

        return response()->json(['message' => 'Matrícula eliminado correctamente']);
    }
}
