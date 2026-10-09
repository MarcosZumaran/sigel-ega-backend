<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Area;
use App\Models\Bimestre;
use App\Models\Calificacion;
use App\Models\ConclusionDescriptiva;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\NivelLogroConsolidado;
use App\Models\Periodo;
use Illuminate\Support\Facades\DB;

class EvaluacionService
{
    // RVM N° 094-2020-MINEDU (Anexo 09): B = 2.5. Alineado con PromedioCompetenciaService.
    public const NIVEL_A_NUMERO = ['AD' => 4.0, 'A' => 3.0, 'B' => 2.5, 'C' => 1.0];

    public static function nivelANumero(?string $nivel): ?float
    {
        return $nivel !== null ? (self::NIVEL_A_NUMERO[$nivel] ?? null) : null;
    }

    public static function numeroANivel(float $promedio): string
    {
        if ($promedio >= 3.5) {
            return 'AD';
        }
        if ($promedio >= 2.5) {
            return 'A';
        }
        if ($promedio >= 1.5) {
            return 'B';
        }

        return 'C';
    }

    /**
     * Nivel de logro de una competencia (área hija) para una matrícula en un bimestre.
     * 1. Si hay actividades registradas: nivel sugerido vía PromedioCompetenciaService (nuevo).
     * 2. Fallback legacy: promedio de calificaciones por tipo_evaluacion.
     */
    public static function calcularNivelLogroCompetencia(int $matriculaId, int $competenciaId, int $bimestreId): ?string
    {
        $hayActividades = Actividad::query()
            ->where('competencia_id', $competenciaId)
            ->where('bimestre_id', $bimestreId)
            ->exists();

        if ($hayActividades) {
            $svc = app(PromedioCompetenciaService::class);
            $resultado = $svc->calcular($matriculaId, $competenciaId, $bimestreId);

            return $resultado['nivel_sugerido'];
        }

        $niveles = Calificacion::query()
            ->where('matricula_id', $matriculaId)
            ->where('area_id', $competenciaId)
            ->where('bimestre_id', $bimestreId)
            ->whereNotNull('nivel_logro')
            ->pluck('nivel_logro');

        if ($niveles->isEmpty()) {
            return null;
        }

        $suma = 0;
        $n = 0;
        foreach ($niveles as $nivel) {
            $num = self::nivelANumero($nivel);
            if ($num !== null) {
                $suma += $num;
                $n++;
            }
        }

        return $n > 0 ? self::numeroANivel($suma / $n) : null;
    }

    /**
     * Nivel de logro de un área padre promediando sus competencias.
     */
    public static function calcularNivelLogroArea(int $matriculaId, int $areaId, int $bimestreId): ?string
    {
        $competencias = Area::query()
            ->where('area_padre_id', $areaId)
            ->where('tipo', 'competencia')
            ->pluck('id');

        $suma = 0;
        $n = 0;
        foreach ($competencias as $competenciaId) {
            $nivel = self::calcularNivelLogroCompetencia($matriculaId, $competenciaId, $bimestreId);
            $num = self::nivelANumero($nivel);
            if ($num !== null) {
                $suma += $num;
                $n++;
            }
        }

        return $n > 0 ? self::numeroANivel($suma / $n) : null;
    }

    /**
     * Nivel final anual de una competencia promediando los 4 bimestres.
     */
    public static function calcularNivelFinalAnual(int $matriculaId, int $competenciaId, int $periodoId): ?string
    {
        $bimestreIds = Bimestre::query()->where('periodo_id', $periodoId)->pluck('id');

        $suma = 0;
        $n = 0;
        foreach ($bimestreIds as $bimestreId) {
            $nivel = self::calcularNivelLogroCompetencia($matriculaId, $competenciaId, $bimestreId);
            $num = self::nivelANumero($nivel);
            if ($num !== null) {
                $suma += $num;
                $n++;
            }
        }

        return $n > 0 ? self::numeroANivel($suma / $n) : null;
    }

    /**
     * Consolida los niveles de un periodo: por cada matrícula, competencia y bimestre
     * guarda el nivel calculado (upsert, es_final=false).
     *
     * @return array{estudiantes: int, niveles: int}
     */
    public static function consolidarPeriodo(int $periodoId): array
    {
        $matriculas = Matricula::query()->where('periodo_id', $periodoId)->pluck('id');
        $competencias = Area::query()->where('tipo', 'competencia')->get(['id', 'area_padre_id']);
        $bimestres = Bimestre::query()->where('periodo_id', $periodoId)->pluck('id');

        $niveles = 0;
        foreach ($matriculas as $matriculaId) {
            foreach ($bimestres as $bimestreId) {
                foreach ($competencias as $competencia) {
                    $nivel = self::calcularNivelLogroCompetencia($matriculaId, $competencia->id, $bimestreId);
                    if ($nivel === null) {
                        continue;
                    }
                    NivelLogroConsolidado::query()->updateOrCreate(
                        [
                            'matricula_id' => $matriculaId,
                            'competencia_id' => $competencia->id,
                            'bimestre_id' => $bimestreId,
                            'es_final' => false,
                        ],
                        // NOTA: la columna de niveles_logro_consolidados se llama 'nivel'
                        // (no 'nivel_logro'; ese nombre vive en calificaciones).
                        ['area_id' => $competencia->area_padre_id, 'nivel' => $nivel]
                    );
                    $niveles++;
                }
            }
        }

        return ['estudiantes' => $matriculas->count(), 'niveles' => $niveles];
    }

    /**
     * Datos del Informe de Progreso de un estudiante en un periodo.
     */
    public static function generarDatosInformeProgreso(int $estudianteId, ?int $periodoId = null): array
    {
        $periodoId ??= (int) Periodo::query()->where('activo', true)->firstOrFail()->id;
        $estudiante = Estudiante::query()->findOrFail($estudianteId);
        $periodo = Periodo::query()->findOrFail($periodoId);
        $matricula = Matricula::query()
            ->where('estudiante_id', $estudianteId)
            ->where('periodo_id', $periodoId)
            ->first();
        $bimestres = Bimestre::query()->where('periodo_id', $periodoId)->orderBy('numero')->get();
        $nivelId = $matricula?->seccion?->grado?->nivel_id;
        $areas = Area::query()->whereNull('area_padre_id')
            ->when($nivelId, fn ($q) => $q->where(fn ($w) => $w->where('nivel_id', $nivelId)->orWhereNull('nivel_id')))
            ->orderBy('id')->get();

        $areasData = [];
        foreach ($areas as $area) {
            $competencias = Area::query()
                ->where('area_padre_id', $area->id)
                ->where('tipo', 'competencia')
                ->orderBy('id')
                ->get();

            $compData = [];
            foreach ($competencias as $competencia) {
                $bims = [];
                foreach ($bimestres as $bimestre) {
                    $nivel = $matricula
                        ? self::calcularNivelLogroCompetencia($matricula->id, $competencia->id, $bimestre->id)
                        : null;
                    $conclusion = $matricula
                        ? ConclusionDescriptiva::query()
                            ->where('matricula_id', $matricula->id)
                            ->where('competencia_id', $competencia->id)
                            ->where('bimestre_id', $bimestre->id)
                            ->value('texto')
                        : null;
                    $bims[] = [
                        'numero' => $bimestre->numero,
                        'nivel' => $nivel,
                        'conclusion' => $conclusion,
                    ];
                }
                $nivelFinal = $matricula
                    ? self::calcularNivelFinalAnual($matricula->id, $competencia->id, $periodoId)
                    : null;
                $compData[] = [
                    'id' => $competencia->id,
                    'nombre' => $competencia->nombre,
                    'bimestres' => $bims,
                    'nivel_final' => $nivelFinal,
                ];
            }

            $nivelArea = null;
            if ($matricula && $bimestres->isNotEmpty()) {
                $suma = 0;
                $n = 0;
                foreach ($bimestres as $bimestre) {
                    $num = self::nivelANumero(self::calcularNivelLogroArea($matricula->id, $area->id, $bimestre->id));
                    if ($num !== null) {
                        $suma += $num;
                        $n++;
                    }
                }
                $nivelArea = $n > 0 ? self::numeroANivel($suma / $n) : null;
            }

            $areasData[] = [
                'id' => $area->id,
                'nombre' => $area->nombre,
                'nivel_logro_area' => $nivelArea,
                'competencias' => $compData,
            ];
        }

        return [
            'estudiante' => $estudiante,
            'periodo' => $periodo,
            'areas' => $areasData,
        ];
    }
}
