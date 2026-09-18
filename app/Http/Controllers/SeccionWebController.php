<?php

namespace App\Http\Controllers;

use App\Services\SeccionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeccionWebController extends Controller
{
    public function __construct(private readonly SeccionService $service) {}

    public function index(Request $request): Response
    {
        $q = \App\Models\Seccion::with(['grado.nivel:id,nombre', 'docente:id,nombres,apellidos'])
            ->withCount('matriculas');

        if ($request->filled('grado_id')) {
            $q->where('grado_id', $request->integer('grado_id'));
        }
        if ($request->filled('turno')) {
            $q->where('turno', $request->input('turno'));
        }

        return Inertia::render('Secciones/Index', [
            'secciones' => $q->orderBy('id')->paginate(15)->withQueryString(),
            'grados' => \App\Models\Grado::with('nivel:id,nombre')->orderBy('id')->get(['id', 'nombre', 'nivel_id']),
            'filtros' => $request->only(['grado_id', 'turno']),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Secciones'],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Secciones/Create', [
            'grados' => \App\Models\Grado::with('nivel:id,nombre')->orderBy('id')->get(['id', 'nombre', 'nivel_id']),
            'docentes' => \App\Models\Docente::orderBy('apellidos')->get(['id', 'nombres', 'apellidos']),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Secciones', 'href' => route('secciones.index')],
                ['label' => 'Nueva'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grado_id' => 'required|integer|exists:grados,id',
            'nombre' => 'required|string|max:10',
            'turno' => 'required|in:manana,tarde',
            'vacantes' => 'required|integer|min:0',
            'docente_id' => 'nullable|integer|exists:docentes,id',
        ]);

        $seccion = $this->service->create($data);

        return redirect()->route('secciones.show', $seccion)->with('success', 'Sección creada correctamente.');
    }

    public function show(int $id): Response
    {
        $seccion = $this->service->getById($id);
        $seccion->load(['grado.nivel:id,nombre', 'docente:id,nombres,apellidos,dni']);
        $ocupadas = $seccion->matriculas()->count();

        return Inertia::render('Secciones/Show', [
            'seccion' => $seccion,
            'ocupadas' => $ocupadas,
            'matriculas' => $seccion->matriculas()
                ->with('estudiante:id,nombres,apellidos,dni')
                ->orderBy('id')
                ->paginate(15),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Secciones', 'href' => route('secciones.index')],
                ['label' => ($seccion->grado->nombre ?? '').' “'.$seccion->nombre.'”'],
            ],
        ]);
    }

    public function edit(int $id): Response
    {
        $seccion = $this->service->getById($id);

        return Inertia::render('Secciones/Edit', [
            'seccion' => $seccion,
            'grados' => \App\Models\Grado::with('nivel:id,nombre')->orderBy('id')->get(['id', 'nombre', 'nivel_id']),
            'docentes' => \App\Models\Docente::orderBy('apellidos')->get(['id', 'nombres', 'apellidos']),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Secciones', 'href' => route('secciones.index')],
                ['label' => 'Editar'],
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'grado_id' => 'sometimes|integer|exists:grados,id',
            'nombre' => 'sometimes|string|max:10',
            'turno' => 'sometimes|in:manana,tarde',
            'vacantes' => 'sometimes|integer|min:0',
            'docente_id' => 'nullable|integer|exists:docentes,id',
        ]);

        $seccion = $this->service->update($id, $data);

        return redirect()->route('secciones.show', $seccion)->with('success', 'Sección actualizada.');
    }

    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);
        } catch (\App\Exceptions\EnUsoException $e) {
            return back()->withErrors(['seccion' => $e->getMessage()]);
        }

        return redirect()->route('secciones.index')->with('success', 'Sección eliminada.');
    }

    public function restore(int $id)
    {
        $this->service->restore($id);

        return back()->with('success', 'Sección restaurada.');
    }
}
