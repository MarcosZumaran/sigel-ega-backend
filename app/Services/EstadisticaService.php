<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EstadisticaService
{
    public function periodoActivo(): ?Periodo
    {
        return Periodo::where('activo', true)->first();
    }

    public function getDashboard(): array
    {
        $periodo = $this->periodoActivo();

        $hoy = Asistencia::whereDate('fecha', today())->get()->groupBy('estado')->map->count();

        return [
            'total_estudiantes' => Estudiante::count(),
            'total_matriculas' => $periodo ? Matricula::where('periodo_id', $periodo->id)->count() : 0,
            'total_secciones' => Seccion::count(),
            'periodo_activo' => $periodo ? ['id' => $periodo->id, 'nombre' => $periodo->nombre] : null,
            'asistencia_hoy' => [
                'presentes' => $hoy->get('presente', 0),
                'tardanzas' => $hoy->get('tardia', 0),
                'ausentes' => $hoy->get('ausente', 0),
                'justificados' => $hoy->get('justificado', 0),
                'total' => $hoy->sum(),
            ],
        ];
    }

    public function getMatriculasPorNivel(?int $periodoId = null): Collection
    {
        $periodoId ??= $this->periodoActivo()?->id;

        return Matricula::query()
            ->when($periodoId, fn ($q) => $q->where('matriculas.periodo_id', $periodoId))
            ->join('secciones', 'secciones.id', '=', 'matriculas.seccion_id')
            ->join('grados', 'grados.id', '=', 'secciones.grado_id')
            ->join('niveles', 'niveles.id', '=', 'grados.nivel_id')
            ->select('niveles.nombre as nivel', DB::raw('COUNT(*) as total'))
            ->groupBy('niveles.id', 'niveles.nombre')
            ->orderBy('niveles.nombre')
            ->get()
            ->map(fn ($r) => ['nivel' => $r->nivel, 'total' => (int) $r->total])
            ->values();
    }

    public function getLogrosCneb(?int $periodoId = null, ?int $seccionId = null): Collection
    {
        $periodoId ??= $this->periodoActivo()?->id;

        $base = Calificacion::query()
            ->whereNotNull('nivel_logro')
            ->whereHas('matricula', function ($q) use ($periodoId, $seccionId) {
                $q->when($periodoId, fn ($qq) => $qq->where('periodo_id', $periodoId))
                    ->when($seccionId, fn ($qq) => $qq->where('seccion_id', $seccionId));
            });

        $conteo = (clone $base)
            ->select('nivel_logro', DB::raw('COUNT(*) as total'))
            ->groupBy('nivel_logro')
            ->pluck('total', 'nivel_logro');

        return collect(['AD', 'A', 'B', 'C'])->map(fn ($nivel) => [
            'nivel' => $nivel,
            'total' => (int) ($conteo->get($nivel, 0)),
        ]);
    }

    public function getAsistenciaMensual(int $periodoId, ?int $anio = null): Collection
    {
        $anio ??= (int) today()->format('Y');

        $filas = Asistencia::query()
            ->whereHas('matricula', fn ($q) => $q->where('periodo_id', $periodoId))
            ->whereYear('fecha', $anio)
            ->select(
                DB::raw("DATE_FORMAT(fecha, '%Y-%m') as mes"),
                'estado',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('mes', 'estado')
            ->orderBy('mes')
            ->get();

        return $filas->groupBy('mes')->map(function ($grupo, $mes) {
            $porEstado = $grupo->pluck('total', 'estado');
            return [
                'mes' => $mes,
                'presentes' => (int) ($porEstado->get('presente', 0)),
                'ausentes' => (int) ($porEstado->get('ausente', 0)),
                'tardanzas' => (int) ($porEstado->get('tardia', 0)),
                'justificados' => (int) ($porEstado->get('justificado', 0)),
            ];
        })->values();
    }

    public function getOcupacionSecciones(int $periodoId): Collection
    {
        $conteo = Matricula::where('periodo_id', $periodoId)
            ->select('seccion_id', DB::raw('COUNT(*) as total'))
            ->groupBy('seccion_id')
            ->pluck('total', 'seccion_id');

        return Seccion::with('grado')->orderBy('id')->get()->map(function ($sec) use ($conteo) {
            $ocupadas = (int) ($conteo->get($sec->id, 0));
            return [
                'seccion' => trim($sec->grado?->nombre.' '.$sec->nombre),
                'grado' => $sec->grado?->nombre,
                'ocupadas' => $ocupadas,
                'vacantes' => max(0, (int) $sec->vacantes - $ocupadas),
                'capacidad' => (int) $sec->vacantes,
            ];
        });
    }
}
