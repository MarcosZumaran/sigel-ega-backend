<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EstudianteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    public function __construct(private readonly EstudianteService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo_estudiante' => 'nullable|string|max:30|unique:estudiantes,codigo_estudiante',
            'dni' => 'nullable|string|size:8|unique:estudiantes,dni',
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'nivel_id' => 'nullable|integer|exists:niveles,id',
            'grado_id' => 'nullable|integer|exists:grados,id',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'apoderado_id' => 'nullable|integer|exists:apoderados,id',
            'padre_id' => 'nullable|integer|exists:padres,id',
        ]);

        // El servicio hereda el apoderado_id del padre seleccionado (padre_id).
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
            'codigo_estudiante' => 'nullable|string|max:30|unique:estudiantes,codigo_estudiante,$id,id',
            'dni' => 'nullable|string|size:8|unique:estudiantes,dni,$id,id',
            'nombres' => 'sometimes|string|max:150',
            'apellidos' => 'sometimes|string|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'nivel_id' => 'nullable|integer|exists:niveles,id',
            'grado_id' => 'nullable|integer|exists:grados,id',
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

        return response()->json(['message' => 'Estudiante eliminado correctamente']);
    }
}
