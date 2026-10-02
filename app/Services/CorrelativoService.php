<?php

namespace App\Services;

use App\Models\Correlativo;
use Illuminate\Support\Facades\DB;

class CorrelativoService
{
    /**
     * Genera el siguiente número correlativo para un tipo de documento.
     * Usa lockForUpdate para evitar colisiones en concurrencia.
     *
     * @return string Número formateado, ej. "OFICIO N° 001-2026-IE-EGA"
     */
    public function siguiente(int $tipoDocumentoId, ?int $anio = null): string
    {
        $anio = $anio ?? (int) date('Y');

        return DB::transaction(function () use ($tipoDocumentoId, $anio) {
            // Buscar o crear el correlativo del año
            $correlativo = Correlativo::where('tipo_documento_id', $tipoDocumentoId)
                ->where('anio', $anio)
                ->lockForUpdate()
                ->first();

            if (! $correlativo) {
                $correlativo = Correlativo::create([
                    'tipo_documento_id' => $tipoDocumentoId,
                    'anio' => $anio,
                    'ultimo_numero' => 0,
                ]);
            }

            // Incrementar
            $correlativo->ultimo_numero++;
            $correlativo->save();

            return $this->formatear($correlativo);
        });
    }

    /**
     * Formatea el número según el patrón configurado.
     */
    public function formatear(Correlativo $correlativo): string
    {
        $tipo = $correlativo->tipoDocumento->nombre ?? 'DOC';

        $formato = $correlativo->formato
            ?? '{tipo} N° {numero:03d}-{anio}-IE-EGA';

        // Reemplazar tokens
        $resultado = str_replace(
            ['{tipo}', '{anio}', '{numero:03d}', '{numero:04d}', '{numero}'],
            [
                strtoupper($tipo),
                $correlativo->anio,
                str_pad($correlativo->ultimo_numero, 3, '0', STR_PAD_LEFT),
                str_pad($correlativo->ultimo_numero, 4, '0', STR_PAD_LEFT),
                $correlativo->ultimo_numero,
            ],
            $formato
        );

        return $resultado;
    }

    /**
     * Consulta el próximo número SIN consumirlo (para previsualización).
     */
    public function proximo(int $tipoDocumentoId, ?int $anio = null): string
    {
        $anio = $anio ?? (int) date('Y');

        $correlativo = Correlativo::firstOrCreate(
            [
                'tipo_documento_id' => $tipoDocumentoId,
                'anio' => $anio,
            ],
            ['ultimo_numero' => 0]
        );

        // Simular el siguiente
        $simulado = clone $correlativo;
        $simulado->ultimo_numero++;

        return $this->formatear($simulado);
    }
}
