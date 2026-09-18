<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Calificacion;
use App\Models\Matricula;
use App\Models\TipoEvaluacion;
use App\Services\CalificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotaWebController extends Controller
{
    public function __construct(private readonly CalificacionService $service)
    {
    }

    public function index(Request $request): Response
    {
        $q = Calificacion::with(['matricula.estudiante', 'area', 'tipoEvaluacion'])->orderByDesc('created_at');

        if ($request->filled('buscar')) {
            $b = trim($request->input('buscar'));
            $q->whereHas('matricula.estudiante', fn ($w) => $w->where('dni', 'like', "%{$b}%")
                ->orWhere('nombres', 'like', "%{$b}%")
                ->orWhere('apellidos', 'like', "%{$b}%"));
        }
        if ($request->filled('area_id')) {
            $q->where('area_id', $request->input('area_id'));
        }

        return Inertia::render('Notas/Index', [
            'notas' => $q->paginate(15)->withQueryString(),
            'filtros' => $request->only(['buscar', 'area_id']),
            'areas' => Area::select(['id', 'nombre'])->orderBy('nombre')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Notas/Create', [
            'matriculas' => Matricula::with('estudiante:id,nombres,apellidos,dni')
                ->select(['id', 'estudiante_id'])->orderByDesc('id')->limit(200)->get(),
            'areas' => Area::select(['id', 'nombre'])->orderBy('nombre')->get(),
            'tipos' => TipoEvaluacion::select(['id', 'nombre'])->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'matricula_id' => 'required|integer|exists:matriculas,id',
            'area_id' => 'required|integer|exists:areas,id',
            'tipo_evaluacion_id' => 'required|integer|exists:tipos_evaluacion,id',
            'nota' => 'nullable|numeric|between:0,20',
            'nivel_logro' => 'nullable|in:AD,A,B,C',
            'escala' => 'nullable|in:literal,vigesimal',
            'es_nota_c' => 'nullable|boolean',
            'motivo_nota_c' => 'nullable|string|max:255',
        ]);

        $nota = $this->service->create($data);

        return redirect()->route('notas.show', $nota->id)->with('success', 'Calificación registrada.');
    }

    public function show(int $id): Response
    {
        $nota = Calificacion::with(['matricula.estudiante', 'area', 'tipoEvaluacion'])->findOrFail($id);

        return Inertia::render('Notas/Show', ['nota' => $nota]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Notas/Edit', [
            'nota' => $this->service->getById($id),
            'areas' => Area::select(['id', 'nombre'])->orderBy('nombre')->get(),
            'tipos' => TipoEvaluacion::select(['id', 'nombre'])->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'matricula_id' => 'sometimes|integer|exists:matriculas,id',
            'area_id' => 'sometimes|integer|exists:areas,id',
            'tipo_evaluacion_id' => 'sometimes|integer|exists:tipos_evaluacion,id',
            'nota' => 'nullable|numeric|between:0,20',
            'nivel_logro' => 'nullable|in:AD,A,B,C',
            'escala' => 'nullable|in:literal,vigesimal',
            'es_nota_c' => 'nullable|boolean',
            'motivo_nota_c' => 'nullable|string|max:255',
        ]);

        $nota = $this->service->update($id, $data);

        return redirect()->route('notas.show', $nota->id)->with('success', 'Calificación actualizada.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('notas.index')->with('success', 'Calificación eliminada.');
    }
}
