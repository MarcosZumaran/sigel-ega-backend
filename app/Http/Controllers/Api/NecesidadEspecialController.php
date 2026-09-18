<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NecesidadEspecialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NecesidadEspecialController extends Controller
{
    public function __construct(private readonly NecesidadEspecialService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'estudiante_id' => ['required','integer','exists:estudiantes,id'],
            'servicio' => ['required','in:SAANEE,SEHO,SAE,OTRO'],
            'tipo_nee' => ['required','string','max:150'],
            'descripcion' => ['nullable','string'],
            'certificado_salud' => ['nullable','string','max:500'],
            'registro_solicitud' => ['nullable','string','max:150'],
            'estado_seho' => ['nullable','in:pendiente,en_atencion,atendido,reincorporado'],
            'codigo_seho' => ['nullable','string','max:100'],
            'nombre_seho' => ['nullable','string','max:200'],
            'fecha_solicitud' => ['nullable','date'],
            'fecha_reincorporacion' => ['nullable','date'],
            'reincorporado' => ['nullable','boolean'],
            'poi_ruta' => ['nullable','string','max:500'],
            'evaluacion_psicopedagogica_ruta' => ['nullable','string','max:500'],
            'ajustes_razonables' => ['nullable','string'],
            'docente_sanee' => ['nullable','string','max:200'],
            'observaciones' => ['nullable','string'],
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
            'estudiante_id' => ['sometimes','integer','exists:estudiantes,id'],
            'servicio' => ['sometimes','in:SAANEE,SEHO,SAE,OTRO'],
            'tipo_nee' => ['sometimes','string','max:150'],
            'descripcion' => ['nullable','string'],
            'certificado_salud' => ['nullable','string','max:500'],
            'registro_solicitud' => ['nullable','string','max:150'],
            'estado_seho' => ['nullable','in:pendiente,en_atencion,atendido,reincorporado'],
            'codigo_seho' => ['nullable','string','max:100'],
            'nombre_seho' => ['nullable','string','max:200'],
            'fecha_solicitud' => ['nullable','date'],
            'fecha_reincorporacion' => ['nullable','date'],
            'reincorporado' => ['nullable','boolean'],
            'poi_ruta' => ['nullable','string','max:500'],
            'evaluacion_psicopedagogica_ruta' => ['nullable','string','max:500'],
            'ajustes_razonables' => ['nullable','string'],
            'docente_sanee' => ['nullable','string','max:200'],
            'observaciones' => ['nullable','string'],
        ]);
        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Necesidad especial eliminada.']);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }
}
