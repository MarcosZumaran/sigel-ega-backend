<?php

namespace App\Services\Siagie;

/**
 * Mapea columnas de archivos SIAGIE a campos internos y viceversa.
 * Lee la definición desde config/siagie.php para permitir cambios sin tocar código.
 */
class SiagieColumnMapper
{
    /**
     * Encuentra el índice de una columna en el header del Excel.
     * Prueba primero el nombre oficial, luego alternativas.
     */
    public function encontrarColumna(array $header, string $clave): ?int
    {
        $definicion = config("siagie.import.columnas.{$clave}");
        if (! $definicion) {
            return null;
        }

        $candidatos = array_merge(
            [$definicion['columna']],
            $definicion['alternativas'] ?? []
        );

        foreach ($candidatos as $candidato) {
            $normalizado = $this->normalizar($candidato);
            foreach ($header as $idx => $col) {
                if ($this->normalizar((string) $col) === $normalizado) {
                    return $idx;
                }
            }
        }

        return null;
    }

    /**
     * Mapea una fila cruda del Excel a un array de campos internos.
     */
    public function mapearFila(array $header, array $fila): array
    {
        $resultado = [];
        foreach (config('siagie.import.columnas') as $clave => $definicion) {
            $idx = $this->encontrarColumna($header, $clave);
            $valor = $idx !== null ? ($fila[$idx] ?? null) : null;

            // Aplicar default si no viene
            if (($valor === null || $valor === '') && isset($definicion['default'])) {
                $valor = $definicion['default'];
            }

            $resultado[$clave] = $valor;
        }

        return $resultado;
    }

    /**
     * Valida el nombre del archivo contra el patrón SIAGIE.
     */
    public function validarNombreArchivo(string $nombre): bool
    {
        if (! config('siagie.nombre_archivo.validar_al_importar')) {
            return true; // Validación desactivada
        }
        $patron = config('siagie.nombre_archivo.patron_regex');

        return (bool) preg_match($patron, $nombre);
    }

    /**
     * Normaliza un string para comparación (lowercase, sin tildes, sin espacios extra).
     */
    private function normalizar(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', ' ', '_'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u', '', ''],
            $s
        );

        return $s;
    }

    /**
     * Genera las columnas de export según la configuración.
     */
    public function columnasExport(): array
    {
        return config('siagie.export.columnas', []);
    }
}
