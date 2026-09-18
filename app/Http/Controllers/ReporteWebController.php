<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use App\Models\Seccion;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReporteWebController extends Controller
{
    public function __construct(private readonly ReporteService $service) {}

    public function index(Request $request): Response
    {
        $request->validate([
            'tipo' => 'nullable|string|max:50',
            'periodo_id' => 'nullable|integer|exists:periodos,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
            'formato' => 'nullable|in:pdf,excel,csv',
        ]);

        return Inertia::render('Reportes/Index', [
            'reportes' => $this->service->paginate($request),
            'filtros' => $request->only(['tipo', 'periodo_id', 'seccion_id', 'formato']),
            'periodos' => Periodo::orderByDesc('anio')->get(['id', 'nombre', 'anio']),
            'secciones' => Seccion::with('grado:id,nombre')->orderBy('id')->get(['id', 'nombre', 'grado_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Reportes/Create', [
            'tipos' => ['matriculas', 'asistencia', 'notas', 'auxiliar', 'general'],
            'periodos' => Periodo::orderByDesc('anio')->get(['id', 'nombre', 'anio', 'activo']),
            'secciones' => Seccion::with('grado:id,nombre')->orderBy('id')->get(['id', 'nombre', 'grado_id']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo' => 'required|string|max:50',
            'periodo_id' => 'nullable|integer|exists:periodos,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
            'formato' => 'nullable|in:pdf,excel,csv',
            'parametros' => 'nullable|array',
        ]);
        $data['generado_por'] = $request->user()->id;

        $reporte = $this->service->generar($data, 'web');

        return redirect()->route('reportes.show', $reporte->id)
            ->with('success', "Reporte {$reporte->tipo} generado correctamente.");
    }

    public function show(int $id): Response
    {
        return Inertia::render('Reportes/Show', [
            'reporte' => $this->service->getById($id),
        ]);
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return redirect()->route('reportes.index')->with('success', 'Reporte eliminado.');
    }

    public function restore(int $id)
    {
        $this->service->restore($id);

        return redirect()->route('reportes.index')->with('success', 'Reporte restaurado.');
    }
}
