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

    public function registro(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'estudiante.modo' => 'required|in:existente,nuevo',
            'estudiante.id' => 'nullable|integer|exists:estudiantes,id',
            'estudiante.dni' => 'nullable|string|size:8|unique:estudiantes,dni',
            'estudiante.nombres' => 'nullable|string|max:150',
            'estudiante.apellidos' => 'nullable|string|max:150',
            'estudiante.fecha_nacimiento' => 'nullable|date|before:today',
            'estudiante.sexo' => 'nullable|in:M,F',
            'estudiante.direccion' => 'nullable|string|max:200',
            'estudiante.telefono' => 'nullable|string|max:20',
            'estudiante.email' => 'nullable|string|email|max:100',
            'estudiante.nivel_id' => 'nullable|integer|exists:niveles,id',
            'estudiante.grado_id' => 'nullable|integer|exists:grados,id',
            'estudiante.estado_id' => 'nullable|integer|exists:estados,id',
            'padre.modo' => 'nullable|in:existente,nuevo,ninguno',
            'padre.id' => 'nullable|integer|exists:padres,id',
            'padre.dni' => 'nullable|string|size:8|unique:padres,dni',
            'padre.nombres' => 'nullable|string|max:150',
            'padre.apellidos' => 'nullable|string|max:150',
            'padre.telefono' => 'nullable|string|max:20',
            'padre.email' => 'nullable|string|email|max:100',
            'padre.direccion' => 'nullable|string|max:200',
            'padre.ocupacion' => 'nullable|string|max:100',
            'padre.estado_id' => 'nullable|integer|exists:estados,id',
            'matricula.seccion_id' => 'required|integer|exists:secciones,id',
            'matricula.periodo_id' => 'required|integer|exists:periodos,id',
            'matricula.tipo_matricula_id' => 'required|integer|exists:tipos_matricula,id',
            'matricula.fecha' => 'nullable|date',
            'matricula.estado_id' => 'nullable|integer|exists:estados,id',
            'matricula.observaciones' => 'nullable|string',
        ]);

        if (($payload['padre']['modo'] ?? 'ninguno') === 'ninguno') {
            $payload['padre'] = null;
        }

        $model = $this->service->matricular($payload);

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
