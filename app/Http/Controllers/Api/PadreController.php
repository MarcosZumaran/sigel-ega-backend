<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PadreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PadreController extends Controller
{
    public function __construct(private readonly PadreService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dni' => 'required|string|size:8|unique:padres,dni',
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'ocupacion' => 'nullable|string|max:100',
            'estado_id' => 'nullable|integer|exists:estados,id',
        ]);

        // El servicio crea el apoderado (hub) automáticamente y lo asigna.
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
            'dni' => 'sometimes|string|size:8|unique:padres,dni,$id,id',
            'nombres' => 'sometimes|string|max:150',
            'apellidos' => 'sometimes|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'ocupacion' => 'nullable|string|max:100',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'apoderado_id' => 'nullable|integer|exists:apoderados,id',
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

        return response()->json(['message' => 'Padre de Familia eliminado correctamente']);
    }
}
