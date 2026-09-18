<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AsistenciaPersonalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsistenciaPersonalController extends Controller
{
    public function __construct(private readonly AsistenciaPersonalService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filtros = $request->only(['personal_id', 'desde', 'hasta', 'estado']);
        $perPage = (int) $request->input('per_page', 15);

        if (!empty(array_filter($filtros))) {
            return response()->json($this->service->paginate($filtros, $perPage));
        }

        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'personal_id' => 'required|integer|exists:personal,id',
            'fecha' => 'required|date',
            'hora_entrada' => 'nullable|date_format:H:i:s|required_without:hora_salida',
            'hora_salida' => 'nullable|date_format:H:i:s',
            'estado' => 'nullable|in:presente,ausente,tardia,justificado,permiso,comision,vacaciones',
            'descripcion_justificacion' => 'nullable|string',
        ]);

        $data['registrado_por'] = $request->user()?->id;

        $model = $this->service->create($data);

        return response()->json($model->load(['personal', 'registradoPor', 'archivos']), 201);
    }

    public function marcar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'hora_entrada' => 'nullable|date_format:H:i:s|required_without:hora_salida',
            'hora_salida' => 'nullable|date_format:H:i:s',
            'estado' => 'nullable|in:presente,ausente,tardia,justificado,permiso,comision,vacaciones',
            'descripcion_justificacion' => 'nullable|string',
        ]);

        $model = $this->service->marcarPropia($request->user()->id, $data);

        return response()->json($model->load(['personal', 'registradoPor']), 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'personal_id' => 'sometimes|integer|exists:personal,id',
            'fecha' => 'sometimes|date',
            'hora_entrada' => 'nullable|date_format:H:i:s',
            'hora_salida' => 'nullable|date_format:H:i:s',
            'estado' => 'nullable|in:presente,ausente,tardia,justificado,permiso,comision,vacaciones',
            'descripcion_justificacion' => 'nullable|string',
        ]);

        $model = $this->service->update($id, $data);

        return response()->json($model->load(['personal', 'archivos']));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Asistencia de personal eliminada correctamente']);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }

    public function justificar(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'descripcion_justificacion' => 'nullable|string',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $archivos = $request->file('archivos', []);
        if (!is_array($archivos)) {
            $archivos = [$archivos];
        }

        $model = $this->service->justificar($id, $request->input('descripcion_justificacion'), $archivos);

        return response()->json($model);
    }
}
