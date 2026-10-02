<?php

namespace App\Services\Siagie;

use App\Models\Seccion;

/**
 * Aplica las reglas CNEB de calificación según nivel y grado.
 */
class SiagieReglasCneb
{
    /**
     * Obtiene las reglas aplicables a una sección.
     */
    public function reglasParaSeccion(Seccion $seccion): array
    {
        $grado = $seccion->grado;
        $nivel = mb_strtolower($grado->nivel->nombre);
        $numeroGrado = (int) filter_var($grado->nombre, FILTER_SANITIZE_NUMBER_INT);

        $reglas = config('siagie.reglas_cneb');

        // Inicial: aplica a todos los grados
        if (str_contains($nivel, 'inicial')) {
            return $reglas['inicial'] + ['nivel' => 'inicial', 'grado' => $numeroGrado];
        }

        // Primaria
        if (str_contains($nivel, 'primaria')) {
            if ($numeroGrado === 1) {
                return $reglas['primaria']['1'] + ['nivel' => 'primaria', 'grado' => 1];
            }

            return $reglas['primaria']['2-6'] + ['nivel' => 'primaria', 'grado' => $numeroGrado];
        }

        // Secundaria
        if (str_contains($nivel, 'secundaria')) {
            if ($numeroGrado >= 1 && $numeroGrado <= 4) {
                return $reglas['secundaria']['1-4'] + ['nivel' => 'secundaria', 'grado' => $numeroGrado];
            }
            if ($numeroGrado === 5) {
                return $reglas['secundaria']['5'] + ['nivel' => 'secundaria', 'grado' => 5];
            }
        }

        // Fallback conservador
        return [
            'escala' => 'literal',
            'nota_c_permitida' => false,
            'requiere_conclusion_c' => false,
            'nivel' => 'desconocido',
            'grado' => $numeroGrado,
        ];
    }

    /**
     * Valida si una nota es válida para la sección.
     */
    public function validarNota(array $datos, array $reglas): array
    {
        $errores = [];
        $nivel = strtoupper(trim((string) ($datos['nivel_logro'] ?? '')));
        $conclusion = trim((string) ($datos['motivo_c'] ?? ''));
        $nota = $datos['nota'] ?? null;

        // Si es C y no está permitida
        if ($nivel === 'C' && empty($reglas['nota_c_permitida'])) {
            $errores[] = "Nota 'C' no permitida en {$reglas['nivel']} {$reglas['grado']}° grado";
        }

        // Si es C y requiere conclusión
        if ($nivel === 'C' && ! empty($reglas['requiere_conclusion_c'])) {
            $longitud = mb_strlen($conclusion);
            [$min, $max] = $reglas['longitud_conclusion'] ?? [10, 350];
            if ($longitud < $min || $longitud > $max) {
                $errores[] = "Conclusión descriptiva debe tener entre {$min} y {$max} caracteres (actual: {$longitud})";
            }
        }

        // Si la escala es vigesimal, no se permite literal
        if (($reglas['escala'] ?? '') === 'vigesimal' && ! empty($nivel)) {
            $errores[] = 'En 5° de secundaria solo se permiten notas numéricas (0-20), no niveles literales';
        }

        return $errores;
    }
}
