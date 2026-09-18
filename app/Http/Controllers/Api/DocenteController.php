<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DocenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocenteController extends Controller
{
    public function __construct(private readonly DocenteService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dni' => 'required|string|size:8|unique:docentes,dni',
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'especialidad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
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
            'dni' => 'sometimes|string|size:8|unique:docentes,dni,$id,id',
            'nombres' => 'sometimes|string|max:150',
            'apellidos' => 'sometimes|string|max:150',
            'especialidad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
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

        return response()->json(['message' => 'Docente eliminado correctamente']);
    }
}
