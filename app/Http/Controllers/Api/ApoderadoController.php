<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApoderadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApoderadoController extends Controller
{
    public function __construct(private readonly ApoderadoService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        // Hub sin datos personales: solo identificador único opcional.
        $data = $request->validate([
            'uuid' => 'nullable|string|max:36|unique:apoderados,uuid',
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
            'uuid' => 'nullable|string|max:36|unique:apoderados,uuid,' . $id . ',id',
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

        return response()->json(['message' => 'Apoderado eliminado correctamente']);
    }

    public function padres(int $id): JsonResponse
    {
        return response()->json($this->service->getPadres($id));
    }

    public function estudiantes(int $id): JsonResponse
    {
        return response()->json($this->service->getEstudiantes($id));
    }
}
