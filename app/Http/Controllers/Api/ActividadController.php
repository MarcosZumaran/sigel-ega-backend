<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActividadService;
use App\Services\CalificacionActividadService;
use App\Services\PromedioCompetenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    public function __construct(
        private readonly ActividadService $service,
        private readonly CalificacionActividadService $calificaciones,
        private readonly PromedioCompetenciaService $promedio,
    ) {
    }

    /**
     * Lista actividades con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'competencia_id' => 'nullable|integer|exists:areas,id',
            'bimestre_id' => 'nullable|integer|exists:bimestres,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
        ]);

        return response()->json($this->service->getAll($filters));
    }

    /**
     * Crea una actividad.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'competencia_id' => 'required|integer|exists:areas,id',
            'bimestre_id' => 'required|integer|exists:bimestres,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'titulo' => 'required|string|min:3|max:200',
            'descripcion' => 'nullable|string|max:1000',
            'fecha' => 'required|date',
        ]);

        $model = $this->service->create($data);

        return response()->json($model->load(['competencia', 'bimestre', 'seccion']), 201);
    }

    /**
     * Detalle de una actividad.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    /**
     * Actualiza una actividad.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'competencia_id' => 'sometimes|integer|exists:areas,id',
            'bimestre_id' => 'sometimes|integer|exists:bimestres,id',
            'seccion_id' => 'sometimes|integer|exists:secciones,id',
            'titulo' => 'sometimes|string|min:3|max:200',
            'descripcion' => 'nullable|string|max:1000',
            'fecha' => 'sometimes|date',
        ]);

        $model = $this->service->update($id, $data);

        return response()->json($model->load(['competencia', 'bimestre', 'seccion']));
    }

    /**
     * Elimina (soft delete) una actividad.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Actividad eliminada correctamente.']);
    }

    /**
     * Restaura una actividad.
     */
    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }

    /**
     * Lista las calificaciones de todos los alumnos en una actividad.
     */
    public function calificaciones(int $id): JsonResponse
    {
        return response()->json($this->calificaciones->porActividad($id));
    }

    /**
     * Guarda en lote las calificaciones de los alumnos de una actividad.
     */
    public function guardarCalificaciones(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.matricula_id' => 'required|integer|exists:matriculas,id',
            'items.*.nivel_logro' => 'required|string|in:AD,A,B,C',
        ]);

        $resultado = $this->calificaciones->guardarBatch($id, $data['items']);

        return response()->json($resultado);
    }

    /**
     * Calcula el nivel SUGERIDO de una competencia para todos los alumnos
     * de una sección (basado en el promedio de actividades).
     */
    public function sugerirNivel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'seccion_id' => 'required|integer|exists:secciones,id',
            'competencia_id' => 'required|integer|exists:areas,id',
            'bimestre_id' => 'required|integer|exists:bimestres,id',
        ]);

        return response()->json([
            'sugerencias' => $this->promedio->calcularSeccion(
                (int) $data['seccion_id'],
                (int) $data['competencia_id'],
                (int) $data['bimestre_id']
            ),
        ]);
    }
}
