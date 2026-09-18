<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Nivel;
use App\Models\Periodo;
use App\Models\Seccion;
use Inertia\Inertia;
use Inertia\Response;

class EstadisticaWebController extends Controller
{
    public function index(): Response
    {
        $periodoActivo = Periodo::where('activo', true)->first();

        $matriculasQ = Matricula::query();
        if ($periodoActivo) {
            $matriculasQ->where('periodo_id', $periodoActivo->id);
        }

        $matriculasPorNivel = Nivel::withCount(['grados as matriculas_count' => function ($q) use ($periodoActivo) {
            $q->join('secciones', 'secciones.grado_id', '=', 'grados.id')
                ->join('matriculas', 'matriculas.seccion_id', '=', 'secciones.id')
                ->whereNull('matriculas.deleted_at');
            if ($periodoActivo) {
                $q->where('matriculas.periodo_id', $periodoActivo->id);
            }
        }])->get(['id', 'nombre']);

        $asistenciasHoy = Asistencia::whereDate('fecha', today())->get(['estado']);
        $asistenciaResumen = [
            'presente' => $asistenciasHoy->where('estado', 'presente')->count(),
            'ausente' => $asistenciasHoy->where('estado', 'ausente')->count(),
            'tardia' => $asistenciasHoy->where('estado', 'tardia')->count(),
            'justificado' => $asistenciasHoy->where('estado', 'justificado')->count(),
        ];

        $notasQ = Calificacion::query();
        if ($periodoActivo) {
            $notasQ->whereHas('matricula', fn ($q) => $q->where('periodo_id', $periodoActivo->id));
        }
        $notas = $notasQ->get(['nivel_logro', 'nota']);
        $logros = [
            'AD' => $notas->where('nivel_logro', 'AD')->count(),
            'A' => $notas->where('nivel_logro', 'A')->count(),
            'B' => $notas->where('nivel_logro', 'B')->count(),
            'C' => $notas->where('nivel_logro', 'C')->count(),
        ];

        $secciones = Seccion::with('grado:id,nombre')
            ->withCount('matriculas')
            ->orderBy('id')
            ->get(['id', 'nombre', 'grado_id', 'vacantes']);

        return Inertia::render('Estadisticas/Index', [
            'periodo_activo' => $periodoActivo?->only(['id', 'nombre', 'anio']),
            'totales' => [
                'estudiantes' => Estudiante::count(),
                'matriculas' => (clone $matriculasQ)->count(),
                'secciones' => Seccion::count(),
            ],
            'matriculas_por_nivel' => $matriculasPorNivel,
            'asistencia_hoy' => $asistenciaResumen,
            'logros' => $logros,
            'secciones' => $secciones,
        ]);
    }
}
