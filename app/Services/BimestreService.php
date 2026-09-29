<?php

namespace App\Services;

/**
 * Fuente de verdad de las fechas oficiales de calendarización MINEDU
 * por año. Cada IE puede ajustarlas editando el bimestre generado.
 */
class BimestreService
{
    /**
     * @var array<int, array<int, array{inicio: string, fin: string}>>
     */
    public const FECHAS_OFICIALES = [
        2025 => [
            1 => ['inicio' => '03-17', 'fin' => '05-16'],
            2 => ['inicio' => '05-26', 'fin' => '07-25'],
            3 => ['inicio' => '08-11', 'fin' => '10-10'],
            4 => ['inicio' => '10-20', 'fin' => '12-19'],
        ],
        2026 => [
            1 => ['inicio' => '03-16', 'fin' => '05-15'],
            2 => ['inicio' => '05-25', 'fin' => '07-24'],
            3 => ['inicio' => '08-10', 'fin' => '10-09'],
            4 => ['inicio' => '10-19', 'fin' => '12-18'],
        ],
    ];

    public static function esOficial(int $anio): bool
    {
        return isset(self::FECHAS_OFICIALES[$anio]);
    }

    /**
     * @return array<int, array{inicio: string, fin: string}>
     */
    public static function rangosParaAnio(int $anio): array
    {
        if (isset(self::FECHAS_OFICIALES[$anio])) {
            return self::FECHAS_OFICIALES[$anio];
        }

        // Cálculo genérico: 36 semanas lectivas desde el segundo lunes de
        // marzo, en 4 bloques de 9 semanas (lunes a viernes).
        $marzo = new \DateTimeImmutable("{$anio}-03-01");
        $offset = (8 - (int) $marzo->format('N')) % 7;
        $inicio = $marzo->modify("+{$offset} days")->modify('+7 days');

        $rangos = [];
        $cursor = $inicio;
        for ($numero = 1; $numero <= 4; $numero++) {
            $fin = $cursor->modify('+62 days'); // 9 semanas: lunes + 62 días = viernes
            $rangos[$numero] = [
                'inicio' => $cursor->format('m-d'),
                'fin' => $fin->format('m-d'),
            ];
            $cursor = $fin->modify('+3 days'); // siguiente lunes
        }

        return $rangos;
    }
}
