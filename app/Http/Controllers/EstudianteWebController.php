<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Nivel;
use App\Models\Padre;
use App\Services\EstudianteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EstudianteWebController extends Controller
{
    public function __construct(private readonly EstudianteService $service)
    {
    }

    public function index(Request $request): Response
    {
        $q = Estudiante::with(['nivel', 'grado', 'estado', 'apoderado'])->orderBy('apellidos')->orderBy('nombres');

        if ($request->filled('buscar')) {
            $b = trim($request->input('buscar'));
            $q->where(function ($w) use ($b) {
                $w->where('dni', 'like', "%{$b}%")
                    ->orWhere('nombres', 'like', "%{$b}%")
                    ->orWhere('apellidos', 'like', "%{$b}%")
                    ->orWhere('codigo_estudiante', 'like', "%{$b}%");
            });
        }

        return Inertia::render('Estudiantes/Index', [
            'estudiantes' => $q->paginate(15)->withQueryString(),
            'filtros' => $request->only('buscar'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Estudiantes/Create', [
            'padres' => Padre::select(['id', 'dni', 'nombres', 'apellidos', 'apoderado_id'])
                ->orderBy('apellidos')->orderBy('nombres')->get(),
            'niveles' => Nivel::select(['id', 'nombre'])->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'codigo_estudiante' => 'nullable|string|max:30|unique:estudiantes,codigo_estudiante',
            'dni' => 'nullable|string|size:8|unique:estudiantes,dni',
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'nivel_id' => 'nullable|integer|exists:niveles,id',
            'grado_id' => 'nullable|integer|exists:grados,id',
            'estado_id' => 'nullable|integer|exists:estados,id',
            // Selector de padre: el servicio hereda su apoderado_id.
            'padre_id' => 'nullable|integer|exists:padres,id',
            // Caso excepcional (sin padre): apoderado directo.
            'apoderado_id' => 'nullable|integer|exists:apoderados,id',
        ]);

        // El servicio hereda el apoderado del padre seleccionado.
        $estudiante = $this->service->create($data);

        return redirect()->route('estudiantes.show', $estudiante->id)
            ->with('success', 'Estudiante creado y vinculado al apoderado #' . $estudiante->apoderado_id . '.');
    }

    public function show(int $id): Response
    {
        $estudiante = Estudiante::with(['nivel', 'grado', 'estado', 'apoderado', 'matriculas.seccion', 'matriculas.periodo'])
            ->findOrFail($id);

        return Inertia::render('Estudiantes/Show', ['estudiante' => $estudiante]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Estudiantes/Edit', [
            'estudiante' => $this->service->getById($id),
            'padres' => Padre::select(['id', 'dni', 'nombres', 'apellidos', 'apoderado_id'])
                ->orderBy('apellidos')->orderBy('nombres')->get(),
            'niveles' => Nivel::select(['id', 'nombre'])->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'codigo_estudiante' => 'nullable|string|max:30|unique:estudiantes,codigo_estudiante,' . $id . ',id',
            'dni' => 'nullable|string|size:8|unique:estudiantes,dni,' . $id . ',id',
            'nombres' => 'sometimes|string|max:150',
            'apellidos' => 'sometimes|string|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'nivel_id' => 'nullable|integer|exists:niveles,id',
            'grado_id' => 'nullable|integer|exists:grados,id',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'apoderado_id' => 'nullable|integer|exists:apoderados,id',
        ]);

        $estudiante = $this->service->update($id, $data);

        return redirect()->route('estudiantes.show', $estudiante->id)->with('success', 'Estudiante actualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('estudiantes.index')->with('success', 'Estudiante eliminado.');
    }

    public function restore(int $id): RedirectResponse
    {
        $estudiante = $this->service->restore($id);

        return redirect()->route('estudiantes.show', $estudiante->id)->with('success', 'Estudiante restaurado.');
    }
}
