<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AsistenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct(private readonly AsistenciaService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'matricula_id' => 'nullable|integer|exists:matriculas,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
            'periodo_id' => 'nullable|integer|exists:periodos,id',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'estado' => 'nullable|in:presente,ausente,tardia,justificado',
        ]);

        return response()->json($this->service->getAll($filters));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'matricula_id' => 'required|integer|exists:matriculas,id',
            'fecha' => 'required|date',
            'estado' => 'required|in:presente,ausente,tardia,justificado',
            'motivo_justificacion' => 'nullable|string|max:500',
            'archivo_justificacion' => 'nullable|string|max:255',
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
            'fecha' => 'sometimes|date',
            'estado' => 'sometimes|in:presente,ausente,tardia,justificado',
            'motivo_justificacion' => 'nullable|string|max:500',
            'archivo_justificacion' => 'nullable|string|max:255',
        ]);

        $model = $this->service->update($id, $data);

        return response()->json($model);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }

    public function batch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.matricula_id' => 'required|integer|exists:matriculas,id',
            'items.*.fecha' => 'required|date',
            'items.*.estado' => 'required|in:presente,ausente,tardia,justificado',
            'items.*.motivo_justificacion' => 'nullable|string|max:500',
        ]);

        return response()->json($this->service->batchUpsert($data['items']));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Asistencia eliminado correctamente']);
    }
}
