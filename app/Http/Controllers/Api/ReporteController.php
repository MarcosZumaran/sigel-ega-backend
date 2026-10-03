<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;
use App\Services\EvaluacionService;
use App\Services\ReporteService;
use App\Services\WordExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function __construct(private readonly ReporteService $service) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'tipo' => 'nullable|string|max:50',
            'periodo_id' => 'nullable|integer|exists:periodos,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
            'formato' => 'nullable|in:pdf,excel,csv',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json($this->service->paginate($request));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function descargar(int $id): StreamedResponse|JsonResponse
    {
        $rep = $this->service->getById($id);
        $path = $rep->ruta_archivo;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Archivo de reporte no encontrado.'], 404);
        }
        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv; charset=UTF-8',
            default => 'application/octet-stream',
        };
        return Storage::disk('local')->download($path, basename($path), ['Content-Type' => $mime]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tipo' => 'required|string|max:50',
            'periodo_id' => 'nullable|integer|exists:periodos,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
            'formato' => 'nullable|in:pdf,excel,csv',
            'parametros' => 'nullable|array',
        ]);
        $data['generado_por'] = $request->user()?->id;

        return response()->json($this->service->generar($data), 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Reporte eliminado correctamente.']);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }

    public function actaEvaluacion(Request $request, EvaluacionService $evaluacion)
    {
        $data = $request->validate([
            'seccion_id' => 'required|integer|exists:secciones,id',
            'periodo_id' => 'required|integer|exists:periodos,id',
        ]);
        $seccion = Seccion::with('grado')->findOrFail($data['seccion_id']);
        $periodo = Periodo::findOrFail($data['periodo_id']);
        [$areas, $filas] = $this->prepararDatosActa($seccion, $periodo, $evaluacion);

        return Pdf::loadView('reportes.acta-evaluacion', compact('seccion', 'periodo', 'areas', 'filas'))
            ->setPaper('a4', 'landscape')
            ->download("acta-evaluacion-{$seccion->id}-{$periodo->id}.pdf");
    }

    public function actaEvaluacionWord(Request $request, ActaWordService $actaWord)
    {
        $request->validate([
            'seccion_id' => 'required|integer|exists:secciones,id',
            'periodo_id' => 'required|integer|exists:periodos,id',
        ]);
        $seccion = Seccion::with('grado.nivel')->findOrFail($request->seccion_id);
        $periodo = Periodo::findOrFail($request->periodo_id);

        $rutaTemp = $actaWord->generarDocx($seccion, $periodo);

        return response()->download($rutaTemp, "ACTA_{$seccion->id}_{$periodo->id}_".date('Ymd').'.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Construye $areas y $filas para el acta (compartido PDF/Word).
     */
    private function prepararDatosActa(Seccion $seccion, Periodo $periodo, EvaluacionService $evaluacion): array
    {
        $nivelId = $seccion->grado?->nivel_id;
        $areas = Area::query()->whereNull('area_padre_id')
            ->when($nivelId, fn ($q) => $q->where(fn ($w) => $w->where('nivel_id', $nivelId)->orWhereNull('nivel_id')))
            ->with(['areasHijas' => fn ($q) => $q->where('tipo', 'competencia')->orderBy('id')])
            ->orderBy('id')->get();
        $matriculas = Matricula::with('estudiante')
            ->where('seccion_id', $seccion->id)->where('periodo_id', $periodo->id)
            ->orderBy('id')->get();
        $filas = [];
        foreach ($matriculas as $m) {
            $informe = $evaluacion->generarDatosInformeProgreso($m->estudiante_id, $periodo->id);
            $porArea = [];
            foreach ($informe['areas'] ?? [] as $a) {
                $porArea[$a['id']] = $a;
            }
            $filas[] = ['matricula' => $m, 'areas' => $porArea];
        }

        return [$areas, $filas];
    }

    public function nominaMatricula(Request $request)
    {
        $data = $request->validate([
            'seccion_id' => 'required|integer|exists:secciones,id',
            'periodo_id' => 'required|integer|exists:periodos,id',
        ]);
        $seccion = Seccion::with('grado')->findOrFail($data['seccion_id']);
        $periodo = Periodo::findOrFail($data['periodo_id']);
        $matriculas = $this->prepararDatosNomina($seccion, $periodo);

        return Pdf::loadView('reportes.nomina-matricula', compact('seccion', 'periodo', 'matriculas'))
            ->setPaper('a4')
            ->download("nomina-matricula-{$seccion->id}-{$periodo->id}.pdf");
    }

    public function nominaMatriculaWord(Request $request, WordExportService $word)
    {
        $data = $request->validate([
            'seccion_id' => 'required|integer|exists:secciones,id',
            'periodo_id' => 'required|integer|exists:periodos,id',
        ]);
        $seccion = Seccion::with('grado')->findOrFail($data['seccion_id']);
        $periodo = Periodo::findOrFail($data['periodo_id']);
        $matriculas = $this->prepararDatosNomina($seccion, $periodo);

        $rutaTemp = storage_path('app/private/tmp_'.uniqid().'.docx');
        $word->desdeVista('reportes.nomina-matricula', compact('seccion', 'periodo', 'matriculas'), $rutaTemp);

        return response()->download($rutaTemp, "NOMINA_{$seccion->id}_{$periodo->id}_".date('Ymd').'.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Matrículas con apoderado para la nómina (compartido PDF/Word).
     */
    private function prepararDatosNomina(Seccion $seccion, Periodo $periodo)
    {
        return Matricula::with(['estudiante.apoderado.padres'])
            ->where('seccion_id', $seccion->id)->where('periodo_id', $periodo->id)
            ->orderBy('id')->get();
    }

    public function ordenMerito(Request $request, EvaluacionService $evaluacion)
    {
        $data = $request->validate([
            'grado_id' => 'required|integer|exists:grados,id',
            'periodo_id' => 'required|integer|exists:periodos,id',
        ]);
        $grado = Grado::findOrFail($data['grado_id']);
        $periodo = Periodo::findOrFail($data['periodo_id']);
        $filas = $this->prepararDatosOrdenMerito($grado, $periodo, $evaluacion);

        return Pdf::loadView('reportes.orden-merito', compact('grado', 'periodo', 'filas'))
            ->setPaper('a4')
            ->download("orden-merito-{$grado->id}-{$periodo->id}.pdf");
    }

    public function ordenMeritoWord(Request $request, EvaluacionService $evaluacion, WordExportService $word)
    {
        $data = $request->validate([
            'grado_id' => 'required|integer|exists:grados,id',
            'periodo_id' => 'required|integer|exists:periodos,id',
        ]);
        $grado = Grado::findOrFail($data['grado_id']);
        $periodo = Periodo::findOrFail($data['periodo_id']);
        $filas = $this->prepararDatosOrdenMerito($grado, $periodo, $evaluacion);

        $rutaTemp = storage_path('app/private/tmp_'.uniqid().'.docx');
        $word->desdeVista('reportes.orden-merito', compact('grado', 'periodo', 'filas'), $rutaTemp);

        return response()->download($rutaTemp, "ORDEN_{$grado->id}_{$periodo->id}_".date('Ymd').'.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Filas con puesto para el orden de mérito (compartido PDF/Word).
     */
    private function prepararDatosOrdenMerito(Grado $grado, Periodo $periodo, EvaluacionService $evaluacion): array
    {
        $num = ['AD' => 4, 'A' => 3, 'B' => 2, 'C' => 1];
        $seccionIds = Seccion::where('grado_id', $grado->id)->pluck('id');
        $matriculas = Matricula::with(['estudiante', 'seccion'])
            ->whereIn('seccion_id', $seccionIds)->where('periodo_id', $periodo->id)
            ->orderBy('id')->get();
        $filas = [];
        foreach ($matriculas as $m) {
            $informe = $evaluacion->generarDatosInformeProgreso($m->estudiante_id, $periodo->id);
            $vals = [];
            foreach ($informe['areas'] ?? [] as $a) {
                if (! empty($a['nivel_logro_area']) && isset($num[$a['nivel_logro_area']])) {
                    $vals[] = $num[$a['nivel_logro_area']];
                }
            }
            if ($vals === []) {
                continue;
            }
            $filas[] = ['matricula' => $m, 'promedio' => round(array_sum($vals) / count($vals) * 5, 1)];
        }
        usort($filas, fn ($a, $b) => $b['promedio'] <=> $a['promedio']);
        $puesto = 0;
        $prev = null;
        foreach ($filas as $i => &$f) {
            if ($prev === null || $f['promedio'] < $prev) {
                $puesto = $i + 1;
                $prev = $f['promedio'];
            }
            $f['puesto'] = $puesto;
        }
        unset($f);

        return $filas;
    }
}
