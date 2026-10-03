<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Periodo;
use App\Models\Reporte;
use App\Models\Seccion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    /**
     * Registra un reporte oficial en la tabla `reportes` (RF-29).
     *
     * A diferencia de los reportes internos, los oficiales no se almacenan en Storage:
     * solo se registra el evento para trazabilidad.
     *
     * @param string $tipo      acta-evaluacion | nomina-matricula | orden-merito | fum | boleta
     * @param string $formato   pdf | docx
     * @param array  $contexto  ['periodo_id' => X, 'seccion_id' => Y, 'estudiante_id' => Z, 'grado_id' => W]
     * @param int    $filas     Cantidad de registros procesados
     */
    public function registrarOficial(string $tipo, string $formato, array $contexto, int $filas = 0): Reporte
    {
        return Reporte::create([
            'tipo' => $tipo,
            'periodo_id' => $contexto['periodo_id'] ?? null,
            'seccion_id' => $contexto['seccion_id'] ?? null,
            'formato' => $formato,
            'estado' => 'generado',
            'parametros' => array_merge($contexto, ['filas' => $filas]),
            'generado_por' => auth()->id(),
            'expira_en' => now()->addYear(),
        ]);
    }

    public function generar(array $data, string $origen = 'api'): Reporte
    {
        $data['expira_en'] = now()->addYear();
        $data['estado'] = $origen === 'cache' ? 'cacheado' : 'generado';
        $data['generado_por'] = $data['generado_por'] ?? auth()->id();
        $data['hash'] = hash('sha256', ($data['tipo'] ?? '') . '|' . ($data['periodo_id'] ?? '') . '|' . ($data['seccion_id'] ?? '') . '|' . now()->toDateString());

        $periodo = $data['periodo_id'] ?? 'general';
        $seccion = $data['seccion_id'] ?? 'todas';
        $formato = strtolower($data['formato'] ?? 'pdf');
        $formatoNorm = $formato === 'excel' ? 'xlsx' : $formato;
        $ext = $formatoNorm === 'xlsx' ? 'xlsx' : ($formatoNorm === 'csv' ? 'csv' : 'pdf');
        $data['ruta_archivo'] = "reportes/{$periodo}/{$seccion}/{$data['tipo']}-" . now()->format('Y-m-d_His') . ".{$ext}";

        if ($formatoNorm === 'pdf') {
            $htmlView = $this->resolveVista($data['tipo']);
            $payload = $this->buildPayload($data);
            $pdf = Pdf::loadView($htmlView, $payload);
            Storage::disk('local')->put($data['ruta_archivo'], $pdf->output());
        } elseif (in_array($formatoNorm, ['xlsx', 'csv'], true)) {
            $payload = $this->buildPayload($data);
            $bin = $this->buildExcel($data['tipo'], $payload, $formatoNorm);
            Storage::disk('local')->put($data['ruta_archivo'], $bin);
        } else {
            Storage::disk('local')->put($data['ruta_archivo'], "Reporte {$data['tipo']} generado {$data['hash']}");
        }

        return Reporte::create($data);
    }

    private function resolveVista(string $tipo): string
    {
        $map = [
            'auxiliar' => 'reportes.auxiliar',
            'asistencia' => 'reportes.asistencia',
            'notas' => 'reportes.notas',
            'matriculas' => 'reportes.matriculas',
            'general' => 'reportes.general',
        ];
        $view = $map[$tipo] ?? 'reportes.general';
        return view()->exists($view) ? $view : 'reportes.general';
    }

    private function buildPayload(array $data): array
    {
        $periodo = isset($data['periodo_id']) ? Periodo::find($data['periodo_id']) : null;
        $seccion = isset($data['seccion_id']) ? Seccion::with(['grado.nivel', 'docente'])->find($data['seccion_id']) : null;
        $matriculas = collect();
        $asistencias = collect();
        $notas = collect();
        if ($seccion) {
            $matriculas = \App\Models\Matricula::with(['estudiante', 'periodo'])
                ->where('seccion_id', $seccion->id)
                ->when(isset($data['periodo_id']), fn ($q) => $q->where('periodo_id', $data['periodo_id']))
                ->orderBy('id')->get();
            $ids = $matriculas->pluck('id')->all();
            if ($ids) {
                $asistencias = \App\Models\Asistencia::whereIn('matricula_id', $ids)->orderBy('fecha')->get();
                $notas = \App\Models\Calificacion::with(['area', 'tipoEvaluacion', 'matricula.estudiante'])
                    ->whereIn('matricula_id', $ids)->orderBy('id')->get();
            }
        } elseif (isset($data['periodo_id'])) {
            $matriculas = \App\Models\Matricula::with(['estudiante', 'seccion.grado.nivel', 'periodo'])
                ->where('periodo_id', $data['periodo_id'])->orderBy('id')->limit(500)->get();
        }

        return [
            'periodo' => $periodo,
            'seccion' => $seccion,
            'matriculas' => $matriculas,
            'asistencias' => $asistencias,
            'notas' => $notas,
            'tipo' => $data['tipo'] ?? 'general',
            'generado_en' => now()->format('d/m/Y H:i'),
            'hash' => $data['hash'] ?? '',
        ];
    }

    private function buildExcel(string $tipo, array $payload, string $formato): string
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle(substr($tipo, 0, 31));
        $headers = match ($tipo) {
            'matriculas' => ['#', 'Estudiante', 'DNI', 'Sección', 'Periodo'],
            'asistencia' => ['Fecha', 'Matrícula', 'Estado'],
            'notas', 'auxiliar' => ['Estudiante', 'Área', 'Evaluación', 'Nota', 'Nivel'],
            default => ['Dato', 'Valor'],
        };
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:' . chr(64 + count($headers)) . '1')->getFont()->setBold(true);
        $row = 2;
        if ($tipo === 'matriculas') {
            foreach ($payload['matriculas'] as $m) {
                $sheet->fromArray([$m->id, ($m->estudiante->nombres ?? '') . ' ' . ($m->estudiante->apellidos ?? ''), $m->estudiante->dni ?? '', $m->seccion->nombre ?? '', $m->periodo->nombre ?? ''], null, "A{$row}");
                $row++;
            }
        } elseif ($tipo === 'asistencia') {
            foreach ($payload['asistencias'] as $a) {
                $sheet->fromArray([$a->fecha, $a->matricula_id, $a->estado], null, "A{$row}");
                $row++;
            }
        } elseif (in_array($tipo, ['notas', 'auxiliar'], true)) {
            foreach ($payload['notas'] as $n) {
                $sheet->fromArray([($n->matricula->estudiante->nombres ?? '') . ' ' . ($n->matricula->estudiante->apellidos ?? ''), $n->area->nombre ?? '', $n->tipoEvaluacion->nombre ?? '', $n->nota ?? '', $n->nivel_logro ?? ''], null, "A{$row}");
                $row++;
            }
        } else {
            $sheet->fromArray([$payload['tipo'], $payload['generado_en']], null, 'A2');
        }
        foreach (range('A', chr(64 + count($headers))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        ob_start();
        if ($formato === 'csv') {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($ss);
            $writer->setDelimiter(',');
            $writer->save('php://output');
        } else {
            (new Xlsx($ss))->save('php://output');
        }

        return ob_get_clean();
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
