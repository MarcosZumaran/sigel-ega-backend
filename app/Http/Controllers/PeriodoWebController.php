<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Periodo;
use App\Services\PeriodoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PeriodoWebController extends Controller
{
    public function __construct(private readonly PeriodoService $service) {}

    public function index(): Response
    {
        return Inertia::render('Periodos/Index', [
            'periodos' => Periodo::withCount('matriculas')->orderByDesc('anio')->paginate(15),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Periodos'],
            ],
        ]);
    }

    public function create(): Response
    {
        $ultimo = Periodo::orderByDesc('anio')->first();

        return Inertia::render('Periodos/Create', [
            'sugerido' => $ultimo ? $ultimo->anio + 1 : (int) date('Y'),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Periodos', 'href' => route('periodos.index')],
                ['label' => 'Apertura de año'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'anio' => 'required|integer|min:2000|max:2100|unique:periodos,anio',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'nullable|boolean',
        ]);

        $periodo = DB::transaction(function () use ($data) {
            if (! empty($data['activo'])) {
                Periodo::where('activo', true)->update(['activo' => false]);
            }

            return $this->service->create($data);
        });

        return redirect()->route('periodos.show', $periodo)->with('success', 'Año escolar aperturado correctamente.');
    }

    public function show(int $id): Response
    {
        $periodo = Periodo::withCount('matriculas')->findOrFail($id);
        $periodo->reportes_count = \App\Models\Reporte::where('periodo_id', $periodo->id)->count();

        return Inertia::render('Periodos/Show', [
            'periodo' => $periodo,
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Periodos', 'href' => route('periodos.index')],
                ['label' => $periodo->nombre],
            ],
        ]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Periodos/Edit', [
            'periodo' => $this->service->getById($id),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Periodos', 'href' => route('periodos.index')],
                ['label' => 'Editar'],
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string|max:50',
            'anio' => 'sometimes|integer|min:2000|max:2100|unique:periodos,anio,'.$id.',id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'nullable|boolean',
        ]);

        $periodo = DB::transaction(function () use ($id, $data) {
            if (! empty($data['activo'])) {
                Periodo::where('activo', true)->where('id', '!=', $id)->update(['activo' => false]);
            }

            return $this->service->update($id, $data);
        });

        return redirect()->route('periodos.show', $periodo)->with('success', 'Periodo actualizado.');
    }

    public function activar(int $id)
    {
        DB::transaction(function () use ($id) {
            Periodo::where('activo', true)->update(['activo' => false]);
            $periodo = $this->service->getById($id);
            $periodo->update(['activo' => true]);
        });

        return back()->with('success', 'Periodo activado. Las nuevas matrículas usarán este año.');
    }

    /**
     * Promoción de estudiantes al siguiente grado del mismo nivel.
     */
    public function promocion(int $id)
    {
        $grados = Grado::orderBy('nivel_id')->orderBy('id')->get(['id', 'nivel_id']);
        $siguiente = [];
        foreach ($grados->groupBy('nivel_id') as $lista) {
            $ids = $lista->pluck('id')->values();
            foreach ($ids as $i => $gid) {
                $siguiente[$gid] = $ids[$i + 1] ?? null;
            }
        }

        $promovidos = 0;
        $egresados = 0;

        DB::transaction(function () use ($siguiente, &$promovidos, &$egresados) {
            \App\Models\Estudiante::whereNotNull('grado_id')->chunkById(200, function ($estudiantes) use ($siguiente, &$promovidos, &$egresados) {
                foreach ($estudiantes as $est) {
                    $nx = $siguiente[$est->grado_id] ?? null;
                    if ($nx) {
                        $est->update(['grado_id' => $nx]);
                        $promovidos++;
                    } else {
                        $egresados++;
                    }
                }
            });
        });

        return back()->with('success', "Promoción aplicada: {$promovidos} promovidos, {$egresados} en último grado.");
    }

    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);
        } catch (\App\Exceptions\EnUsoException $e) {
            return back()->withErrors(['periodo' => $e->getMessage()]);
        }

        return redirect()->route('periodos.index')->with('success', 'Periodo eliminado.');
    }

    public function restore(int $id)
    {
        $this->service->restore($id);

        return back()->with('success', 'Periodo restaurado.');
    }
}
