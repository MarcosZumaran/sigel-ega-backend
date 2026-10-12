<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Area;
use App\Models\Bimestre;
use App\Models\CalificacionActividad;
use App\Models\Matricula;
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

    /**
     * Datos batch para la vista matricial (BUG-13).
     *
     * Reemplaza el fan-out N+1 (1 request por actividad + matriculas +
     * sugerencia + catálogos) por UNA sola respuesta con número de
     * queries constante (~5). Las sugerencias se calculan en PHP sobre
     * los datos ya cargados, con la misma semántica que
     * PromedioCompetenciaService::calcular().
     *
     * NOTA: se usa setEagerLoads([]) porque los modelos definen $with
     * por defecto, que reintroduciría el N+1 dentro del batch.
     */
    public function matricialData(Request $request): JsonResponse
    {
        $data = $request->validate([
            'seccion_id' => 'required|integer|exists:secciones,id',
            'bimestre_id' => 'required|integer|exists:bimestres,id',
            'competencia_id' => 'required|integer|exists:areas,id',
        ]);

        $bimestre = Bimestre::query()->setEagerLoads([])->findOrFail($data['bimestre_id']);

        $competencia = Area::query()->setEagerLoads([])
            ->findOrFail($data['competencia_id'], ['id', 'nombre']);

        $actividades = Actividad::query()->setEagerLoads([])
            ->where('competencia_id', $competencia->id)
            ->where('bimestre_id', $bimestre->id)
            ->where('seccion_id', $data['seccion_id'])
            ->orderBy('fecha')
            ->get(['id', 'competencia_id', 'bimestre_id', 'seccion_id', 'titulo', 'fecha']);

        $matriculas = Matricula::query()->setEagerLoads([])
            ->with(['estudiante' => fn ($q) => $q->setEagerLoads([])
                ->select('id', 'dni', 'nombres', 'apellidos')])
            ->where('seccion_id', $data['seccion_id'])
            ->where('periodo_id', $bimestre->periodo_id)
            ->orderBy('id')
            ->get(['id', 'seccion_id', 'estudiante_id']);

        $calificaciones = CalificacionActividad::query()->setEagerLoads([])
            ->whereIn('actividad_id', $actividades->pluck('id'))
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->get(['actividad_id', 'matricula_id', 'nivel_logro']);

        // Sugerencias en memoria (0 queries), misma semántica que calcular().
        $calPorActividad = $calificaciones->groupBy('actividad_id');
        $sugerencias = [];
        foreach ($matriculas as $mat) {
            $suma = 0.0;
            $n = 0;
            $detalle = [];
            foreach ($actividades as $act) {
                foreach ($calPorActividad->get($act->id, []) as $cal) {
                    if ($cal->matricula_id !== $mat->id) {
                        continue;
                    }
                    $num = PromedioCompetenciaService::nivelANumero($cal->nivel_logro);
                    if ($num !== null) {
                        $suma += $num;
                        $n++;
                        $detalle[] = [
                            'actividad_id' => $act->id,
                            'nivel' => $cal->nivel_logro,
                            'valor_numerico' => $num,
                        ];
                    }
                }
            }
            $sugerencias[$mat->id] = $n > 0
                ? [
                    'nivel_sugerido' => PromedioCompetenciaService::numeroANivel($suma / $n),
                    'promedio' => round($suma / $n, 3),
                    'total_actividades' => $actividades->count(),
                    'actividades_evaluadas' => $n,
                    'detalle' => $detalle,
                ]
                : [
                    'nivel_sugerido' => null,
                    'promedio' => null,
                    'total_actividades' => $actividades->count(),
                    'actividades_evaluadas' => 0,
                    'detalle' => [],
                ];
        }

        return response()->json([
            'competencia' => $competencia,
            'actividades' => $actividades,
            'matriculas' => $matriculas,
            'calificaciones' => $calificaciones,
            'sugerencias' => $sugerencias,
        ]);
    }
}
