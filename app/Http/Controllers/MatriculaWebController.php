<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;
use App\Models\TipoMatricula;
use App\Services\MatriculaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MatriculaWebController extends Controller
{
    public function __construct(private readonly MatriculaService $service)
    {
    }

    public function index(Request $request): Response
    {
        $q = Matricula::with(['estudiante', 'seccion.grado', 'periodo', 'estado'])
            ->orderByDesc('created_at');

        if ($request->filled('buscar')) {
            $b = trim($request->input('buscar'));
            $q->whereHas('estudiante', fn ($w) => $w->where('dni', 'like', "%{$b}%")
                ->orWhere('nombres', 'like', "%{$b}%")
                ->orWhere('apellidos', 'like', "%{$b}%"));
        }
        if ($request->filled('periodo_id')) {
            $q->where('periodo_id', $request->input('periodo_id'));
        }
        if ($request->filled('seccion_id')) {
            $q->where('seccion_id', $request->input('seccion_id'));
        }

        return Inertia::render('Matriculas/Index', [
            'matriculas' => $q->paginate(15)->withQueryString(),
            'filtros' => $request->only(['buscar', 'periodo_id', 'seccion_id']),
            'periodos' => Periodo::select(['id', 'nombre', 'anio'])->orderByDesc('anio')->get(),
            'secciones' => Seccion::with('grado:id,nombre')->select(['id', 'nombre', 'grado_id'])->orderBy('nombre')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Matriculas/Create', [
            'secciones' => Seccion::with(['grado:id,nombre,nivel_id', 'grado.nivel:id,nombre'])->select(['id', 'nombre', 'grado_id', 'turno', 'vacantes'])->orderBy('nombre')->get(),
            'periodos' => Periodo::select(['id', 'nombre', 'anio', 'activo'])->orderByDesc('anio')->get(),
            'periodo_activo' => Periodo::where('activo', true)->first(['id', 'nombre', 'anio']),
            'tipos' => TipoMatricula::select(['id', 'nombre'])->orderBy('nombre')->get(),
            'niveles' => \App\Models\Nivel::select(['id', 'nombre'])->orderBy('nombre')->get(),
            'grados' => \App\Models\Grado::select(['id', 'nombre', 'nivel_id'])->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'estudiante_id' => 'required|integer|exists:estudiantes,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'periodo_id' => 'required|integer|exists:periodos,id',
            'tipo_matricula_id' => 'required|integer|exists:tipos_matricula,id',
            'fecha' => 'nullable|date',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'observaciones' => 'nullable|string',
        ]);

        $matricula = $this->service->create($data);

        return redirect()->route('matriculas.show', $matricula->id)->with('success', 'Matrícula registrada.');
    }

    /**
     * Registro unificado: padre + estudiante + matrícula en un solo paso.
     */
    public function registro(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'estudiante.modo' => 'required|in:existente,nuevo',
            'estudiante.id' => 'nullable|integer|exists:estudiantes,id',
            'estudiante.dni' => 'nullable|string|size:8|unique:estudiantes,dni',
            'estudiante.nombres' => 'nullable|string|max:150',
            'estudiante.apellidos' => 'nullable|string|max:150',
            'estudiante.fecha_nacimiento' => 'nullable|date|before:today',
            'estudiante.sexo' => 'nullable|in:M,F',
            'estudiante.direccion' => 'nullable|string|max:200',
            'estudiante.telefono' => 'nullable|string|max:20',
            'estudiante.email' => 'nullable|string|email|max:100',
            'estudiante.nivel_id' => 'nullable|integer|exists:niveles,id',
            'estudiante.grado_id' => 'nullable|integer|exists:grados,id',
            'estudiante.estado_id' => 'nullable|integer|exists:estados,id',
            'padre.modo' => 'nullable|in:existente,nuevo,ninguno',
            'padre.id' => 'nullable|integer|exists:padres,id',
            'padre.dni' => 'nullable|string|size:8|unique:padres,dni',
            'padre.nombres' => 'nullable|string|max:150',
            'padre.apellidos' => 'nullable|string|max:150',
            'padre.telefono' => 'nullable|string|max:20',
            'padre.email' => 'nullable|string|email|max:100',
            'padre.direccion' => 'nullable|string|max:200',
            'padre.ocupacion' => 'nullable|string|max:100',
            'padre.estado_id' => 'nullable|integer|exists:estados,id',
            'matricula.seccion_id' => 'required|integer|exists:secciones,id',
            'matricula.periodo_id' => 'required|integer|exists:periodos,id',
            'matricula.tipo_matricula_id' => 'required|integer|exists:tipos_matricula,id',
            'matricula.fecha' => 'nullable|date',
            'matricula.estado_id' => 'nullable|integer|exists:estados,id',
            'matricula.observaciones' => 'nullable|string',
        ]);

        if (($payload['padre']['modo'] ?? 'ninguno') === 'ninguno') {
            $payload['padre'] = null;
        }

        $matricula = $this->service->matricular($payload);

        return redirect()->route('matriculas.show', $matricula->id)->with('success', 'Matrícula registrada (estudiante + padre + matrícula en un paso).');
    }

    public function show(int $id): Response
    {
        $matricula = Matricula::with(['estudiante', 'seccion.grado', 'periodo', 'tipoMatricula', 'estado', 'calificaciones', 'asistencias'])
            ->findOrFail($id);

        return Inertia::render('Matriculas/Show', ['matricula' => $matricula]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Matriculas/Edit', [
            'matricula' => $this->service->getById($id),
            'estudiantes' => Estudiante::select(['id', 'dni', 'nombres', 'apellidos'])->orderBy('apellidos')->get(),
            'secciones' => Seccion::select(['id', 'nombre'])->orderBy('nombre')->get(),
            'periodos' => Periodo::select(['id', 'nombre', 'anio'])->orderByDesc('anio')->get(),
            'tipos' => TipoMatricula::select(['id', 'nombre'])->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'estudiante_id' => 'sometimes|integer|exists:estudiantes,id',
            'seccion_id' => 'sometimes|integer|exists:secciones,id',
            'periodo_id' => 'sometimes|integer|exists:periodos,id',
            'tipo_matricula_id' => 'sometimes|integer|exists:tipos_matricula,id',
            'fecha' => 'nullable|date',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'observaciones' => 'nullable|string',
        ]);

        $matricula = $this->service->update($id, $data);

        return redirect()->route('matriculas.show', $matricula->id)->with('success', 'Matrícula actualizada.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('matriculas.index')->with('success', 'Matrícula eliminada.');
    }

    public function restore(int $id): RedirectResponse
    {
        $matricula = $this->service->restore($id);

        return redirect()->route('matriculas.show', $matricula->id)->with('success', 'Matrícula restaurada.');
    }
}
