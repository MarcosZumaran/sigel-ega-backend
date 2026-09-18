<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GradoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    public function __construct(private readonly GradoService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nivel_id' => 'required|integer|exists:niveles,id',
            'nombre' => 'required|string|max:100',
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
            'nivel_id' => 'sometimes|integer|exists:niveles,id',
            'nombre' => 'sometimes|string|max:100',
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

        return response()->json(['message' => 'Grado eliminado correctamente']);
    }
}
