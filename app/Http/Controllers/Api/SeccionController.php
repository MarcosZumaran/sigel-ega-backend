<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SeccionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeccionController extends Controller
{
    public function __construct(private readonly SeccionService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'grado_id' => 'required|integer|exists:grados,id',
            'nombre' => 'required|string|max:10',
            'turno' => 'required|in:manana,tarde',
            'vacantes' => 'required|integer|min:0',
            'docente_id' => 'nullable|integer|exists:docentes,id',
        ]);

        $model = $this->service->create($data);

        return response()->json($model, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function vacantes(int $id): JsonResponse
    {
        $seccion = \App\Models\Seccion::findOrFail($id);
        $ocupadas = \App\Models\Matricula::where('seccion_id', $id)->count();
        $vacantes = (int) ($seccion->vacantes ?? 0);

        return response()->json([
            'vacantes' => $vacantes,
            'ocupadas' => $ocupadas,
            'disponibles' => max(0, $vacantes - $ocupadas),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'grado_id' => 'sometimes|integer|exists:grados,id',
            'nombre' => 'sometimes|string|max:10',
            'turno' => 'sometimes|in:manana,tarde',
            'vacantes' => 'sometimes|integer|min:0',
            'docente_id' => 'nullable|integer|exists:docentes,id',
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

        return response()->json(['message' => 'Sección eliminado correctamente']);
    }
}
