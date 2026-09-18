<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Periodo;
use App\Models\Reporte;
use App\Models\Seccion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReporteService
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        $q = Reporte::with(['periodo', 'seccion', 'autor:id,name'])->orderByDesc('created_at');
        if ($request->filled('tipo')) $q->where('tipo', $request->input('tipo'));
        if ($request->filled('periodo_id')) $q->where('periodo_id', $request->input('periodo_id'));
        if ($request->filled('seccion_id')) $q->where('seccion_id', $request->input('seccion_id'));
        if ($request->filled('formato')) $q->where('formato', $request->input('formato'));
        if ($request->filled('desde')) $q->where('created_at', '>=', $request->input('desde'));
        if ($request->filled('hasta')) $q->where('created_at', '<=', $request->input('hasta'));

        if (! $request->filled('desde') && ! $request->filled('hasta')) {
            $q->where('created_at', '>=', now()->subYear());
        }
        $perPage = min((int) $request->input('per_page', 20), 100);

        return $q->paginate($perPage);
    }

    public function getById(int $id): Reporte
    {
        $m = Reporte::with(['periodo', 'seccion', 'autor'])->find($id);
        if (! $m) throw new NotFoundException('Reporte', $id);
        return $m;
    }

    public function generar(array $data, string $origen = 'api'): Reporte
    {
        $data['expira_en'] = now()->addYear();
        $data['estado'] = $origen === 'cache' ? 'cacheado' : 'generado';
        $data['generado_por'] = $data['generado_por'] ?? auth()->id();
        $data['hash'] = hash('sha256', ($data['tipo'] ?? '') . '|' . ($data['periodo_id'] ?? '') . '|' . ($data['seccion_id'] ?? '') . '|' . now()->toDateString());

        $periodo = $data['periodo_id'] ?? 'general';
        $seccion = $data['seccion_id'] ?? 'todas';
        $formato = $data['formato'] ?? 'pdf';
        $data['ruta_archivo'] = "reportes/{$periodo}/{$seccion}/{$data['tipo']}-" . now()->format('Y-m-d_His') . ".{$formato}";

        Storage::disk('local')->put($data['ruta_archivo'], "Reporte {$data['tipo']} generado {$data['hash']}");

        return Reporte::create($data);
    }

    public function cacheNocturno(): int
    {
        $periodoActivo = Periodo::where('activo', true)->first();
        if (! $periodoActivo) return 0;
        $secciones = Seccion::with('grado')->get();
        $count = 0;
        foreach (['auxiliar', 'asistencia'] as $tipo) {
            foreach ($secciones as $sec) {
                $payload = [
                    'tipo' => $tipo,
                    'periodo_id' => $periodoActivo->id,
                    'seccion_id' => $sec->id,
                    'formato' => 'pdf',
                    'parametros' => ['origen' => 'cache_nocturno'],
                ];
                $this->generar($payload, 'cache');
                $count++;
            }
        }

        return $count;
    }

    public function delete(int $id): void
    {
        $m = $this->getById($id);
        $m->delete();
    }

    public function restore(int $id): Reporte
    {
        $m = Reporte::withTrashed()->find($id);
        if (! $m || ! $m->trashed()) throw new NotFoundException('Reporte eliminado', $id);
        $m->restore();
        return $m->refresh();
    }
}
