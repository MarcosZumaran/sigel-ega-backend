<?php

namespace App\Http\Controllers;

use App\Services\GradoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradoWebController extends Controller
{
    public function __construct(private readonly GradoService $service) {}

    public function index(): Response
    {
        return Inertia::render('Grados/Index', [
            'grados' => \App\Models\Grado::with('nivel:id,nombre')
                ->withCount('secciones')
                ->orderBy('id')
                ->paginate(15),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Grados'],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Grados/Create', [
            'niveles' => \App\Models\Nivel::orderBy('nombre')->get(['id', 'nombre']),
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Grados', 'href' => route('grados.index')],
                ['label' => 'Nuevo'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nivel_id' => 'required|integer|exists:niveles,id',
            'nombre' => 'required|string|max:100',
        ]);

        $grado = $this->service->create($data);

        return redirect()->route('grados.show', $grado)->with('success', 'Grado creado correctamente.');
    }

    public function show(int $id): Response
    {
        $grado = \App\Models\Grado::with(['nivel:id,nombre', 'secciones.docente:id,nombres,apellidos'])
            ->withCount('secciones')
            ->findOrFail($id);
        $grado->estudiantes_count = \App\Models\Estudiante::where('grado_id', $grado->id)->count();

        return Inertia::render('Grados/Show', [
            'grado' => $grado,
            'crumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Grados', 'href' => route('grados.index')],
                ['label' => $grado->nombre],
            ],
        ]);
    }

    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);
        } catch (\App\Exceptions\EnUsoException $e) {
            return back()->withErrors(['grado' => $e->getMessage()]);
        }

        return redirect()->route('grados.index')->with('success', 'Grado eliminado.');
    }

    public function restore(int $id)
    {
        $this->service->restore($id);

        return back()->with('success', 'Grado restaurado.');
    }
}
