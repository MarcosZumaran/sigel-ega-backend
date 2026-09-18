<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PersonalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalController extends Controller
{
    public function __construct(private readonly PersonalService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dni' => 'required|string|size:8|unique:personal,dni',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'categoria' => 'required|in:docente,directivo,administrativo,servicio',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:150',
            'fecha_ingreso' => 'nullable|date',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'user_id' => 'nullable|integer|exists:users,id|unique:personal,user_id',
            'foto' => 'nullable|string|max:500',
            'observaciones' => 'nullable|string',
        ]);

        $model = $this->service->create($data);

        return response()->json($model->load(['estado', 'usuario']), 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'dni' => 'sometimes|string|size:8|unique:personal,dni,' . $id . ',id',
            'nombres' => 'sometimes|string|max:100',
            'apellidos' => 'sometimes|string|max:100',
            'categoria' => 'sometimes|in:docente,directivo,administrativo,servicio',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:150',
            'fecha_ingreso' => 'nullable|date',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'user_id' => 'nullable|integer|exists:users,id|unique:personal,user_id,' . $id . ',id',
            'foto' => 'nullable|string|max:500',
            'observaciones' => 'nullable|string',
        ]);

        $model = $this->service->update($id, $data);

        return response()->json($model->load(['estado', 'usuario']));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Personal eliminado correctamente']);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }

    public function asistencias(int $id): JsonResponse
    {
        return response()->json($this->service->getAsistencias($id));
    }
}
