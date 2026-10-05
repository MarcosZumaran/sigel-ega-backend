<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\CalificacionActividad;
use App\Models\Matricula;

/**
 * Calcula el nivel de logro SUGERIDO de una competencia para un alumno
 * en base al promedio de sus actividades, usando la conversión MINEDU.
 *
 * IMPORTANTE: Este nivel es una SUGERENCIA. El docente decide el nivel final.
 *
 * Conversión MINEDU (Anexo 09 RVM 094-2020):
 * C = 1, B = 2.5, A = 3, AD = 4
 */
class PromedioCompetenciaService
{
    /** Conversión oficial MINEDU (Anexo 09). */
    public const NIVEL_A_NUMERO = [
        'AD' => 4.0,
        'A'  => 3.0,
        'B'  => 2.5,
        'C'  => 1.0,
    ];

    /**
     * Convierte nivel literal a numérico (MINEDU).
     */
    public static function nivelANumero(?string $nivel): ?float
    {
        return $nivel !== null ? (self::NIVEL_A_NUMERO[$nivel] ?? null) : null;
    }

    /**
     * Convierte promedio numérico a nivel literal.
     */
    public static function numeroANivel(float $promedio): string
    {
        if ($promedio >= 3.5) return 'AD';
        if ($promedio >= 2.5) return 'A';
        if ($promedio >= 1.5) return 'B';
        return 'C';
    }

    /**
     * Calcula el nivel SUGERIDO de una competencia para un alumno en un bimestre.
     * Promedia los niveles de TODAS las actividades de esa competencia en ese bimestre.
     *
     * @return array{nivel_sugerido: ?string, promedio: ?float, total_actividades: int, detalle: array}
     */
    public function calcular(int $matriculaId, int $competenciaId, int $bimestreId): array
    {
        // Obtener todas las actividades de la competencia + bimestre
        $actividades = Actividad::query()
            ->where('competencia_id', $competenciaId)
            ->where('bimestre_id', $bimestreId)
            ->pluck('id');

        if ($actividades->isEmpty()) {
            return [
                'nivel_sugerido' => null,
                'promedio' => null,
                'total_actividades' => 0,
                'detalle' => [],
            ];
        }

        // Obtener las calificaciones del alumno en esas actividades
        $calificaciones = CalificacionActividad::query()
            ->whereIn('actividad_id', $actividades)
            ->where('matricula_id', $matriculaId)
            ->get(['actividad_id', 'nivel_logro']);

        if ($calificaciones->isEmpty()) {
            return [
                'nivel_sugerido' => null,
                'promedio' => null,
                'total_actividades' => $actividades->count(),
                'detalle' => [],
            ];
        }

        // Promediar
        $suma = 0.0;
        $n = 0;
        $detalle = [];
        foreach ($calificaciones as $cal) {
            $num = self::nivelANumero($cal->nivel_logro);
            if ($num !== null) {
                $suma += $num;
                $n++;
                $detalle[] = [
                    'actividad_id' => $cal->actividad_id,
                    'nivel' => $cal->nivel_logro,
                    'valor_numerico' => $num,
                ];
            }
        }

        if ($n === 0) {
            return [
                'nivel_sugerido' => null,
                'promedio' => null,
                'total_actividades' => $actividades->count(),
                'detalle' => [],
            ];
        }

        $promedio = $suma / $n;

        return [
            'nivel_sugerido' => self::numeroANivel($promedio),
            'promedio' => round($promedio, 3),
            'total_actividades' => $actividades->count(),
            'actividades_evaluadas' => $n,
            'detalle' => $detalle,
        ];
    }

    /**
     * Calcula el nivel sugerido para TODOS los alumnos de una sección.
     *
     * @return array<int, array> keyed by matricula_id
     */
    public function calcularSeccion(int $seccionId, int $competenciaId, int $bimestreId): array
    {
        $matriculas = Matricula::query()->where('seccion_id', $seccionId)->pluck('id');
        $resultado = [];
        foreach ($matriculas as $matId) {
            $resultado[$matId] = $this->calcular($matId, $competenciaId, $bimestreId);
        }
        return $resultado;
    }
}
