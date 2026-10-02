<?php

namespace App\Services;

use App\Models\SiagieLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SiagieLogService
{
    /**
     * Registra un evento de import/export SIAGIE.
     */
    public function registrar(array $data): SiagieLog
    {
        // Calcular resultado automáticamente si no viene
        if (! isset($data['resultado'])) {
            $data['resultado'] = $this->calcularResultado($data);
        }

        return SiagieLog::create([
            'usuario_id' => $data['usuario_id'] ?? Auth::id(),
            'operacion' => $data['operacion'],
            'archivo' => $data['archivo'] ?? null,
            'formato' => $data['formato'] ?? null,
            'periodo_id' => $data['periodo_id'] ?? null,
            'seccion_id' => $data['seccion_id'] ?? null,
            'filas_procesadas' => $data['filas_procesadas'] ?? 0,
            'filas_exitosas' => $data['filas_exitosas'] ?? 0,
            'filas_con_error' => $data['filas_con_error'] ?? 0,
            'errores' => $data['errores'] ?? null,
            'resultado' => $data['resultado'] ?? 'exitoso',
            'duracion_ms' => $data['duracion_ms'] ?? null,
            'ip_address' => $data['ip_address'] ?? Request::ip(),
            'observaciones' => $data['observaciones'] ?? null,
        ]);
    }

    /**
     * Determina el resultado basado en las filas.
     */
    private function calcularResultado(array $data): string
    {
        $exitosas = $data['filas_exitosas'] ?? 0;
        $conError = $data['filas_con_error'] ?? 0;
        $procesadas = $data['filas_procesadas'] ?? 0;

        if ($procesadas === 0) {
            return 'fallido';
        }
        if ($conError === 0) {
            return 'exitoso';
        }
        if ($exitosas > 0) {
            return 'parcial';
        }

        return 'fallido';
    }

    /**
     * Lista logs paginados con filtros.
     */
    public function paginate(array $filtros = [], int $perPage = 20)
    {
        $query = SiagieLog::with(['usuario:id,name,email', 'periodo:id,nombre', 'seccion:id,nombre'])
            ->orderByDesc('created_at');

        if (! empty($filtros['operacion'])) {
            $query->where('operacion', $filtros['operacion']);
        }
        if (! empty($filtros['resultado'])) {
            $query->where('resultado', $filtros['resultado']);
        }
        if (! empty($filtros['periodo_id'])) {
            $query->where('periodo_id', $filtros['periodo_id']);
        }
        if (! empty($filtros['desde'])) {
            $query->whereDate('created_at', '>=', $filtros['desde']);
        }
        if (! empty($filtros['hasta'])) {
            $query->whereDate('created_at', '<=', $filtros['hasta']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Obtiene un log por ID.
     */
    public function getById(int $id): SiagieLog
    {
        return SiagieLog::with(['usuario', 'periodo', 'seccion'])->findOrFail($id);
    }
}
