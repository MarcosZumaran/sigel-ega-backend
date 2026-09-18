<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Matricula;
use App\Services\AsistenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AsistenciaWebController extends Controller
{
    public function __construct(private readonly AsistenciaService $service)
    {
    }

    public function index(Request $request): Response
    {
        $q = Asistencia::with(['matricula.estudiante', 'matricula.seccion'])->orderByDesc('fecha')->orderByDesc('id');

        if ($request->filled('buscar')) {
            $b = trim($request->input('buscar'));
            $q->whereHas('matricula.estudiante', fn ($w) => $w->where('dni', 'like', "%{$b}%")
                ->orWhere('nombres', 'like', "%{$b}%")
                ->orWhere('apellidos', 'like', "%{$b}%"));
        }
        if ($request->filled('estado')) {
            $q->where('estado', $request->input('estado'));
        }
        if ($request->filled('fecha')) {
            $q->whereDate('fecha', $request->input('fecha'));
        }

        return Inertia::render('Asistencias/Index', [
            'asistencias' => $q->paginate(15)->withQueryString(),
            'filtros' => $request->only(['buscar', 'estado', 'fecha']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Asistencias/Create', [
            'matriculas' => Matricula::with('estudiante:id,nombres,apellidos,dni')
                ->select(['id', 'estudiante_id'])->orderByDesc('id')->limit(200)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'matricula_id' => 'required|integer|exists:matriculas,id',
            'fecha' => 'required|date',
            'estado' => 'required|in:presente,ausente,tardia,justificado',
        ]);

        $asistencia = $this->service->create($data);

        return redirect()->route('asistencias.show', $asistencia->id)->with('success', 'Asistencia registrada.');
    }

    public function show(int $id): Response
    {
        $asistencia = Asistencia::with(['matricula.estudiante', 'matricula.seccion'])->findOrFail($id);

        return Inertia::render('Asistencias/Show', ['asistencia' => $asistencia]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Asistencias/Edit', ['asistencia' => $this->service->getById($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'matricula_id' => 'sometimes|integer|exists:matriculas,id',
            'fecha' => 'sometimes|date',
            'estado' => 'sometimes|in:presente,ausente,tardia,justificado',
        ]);

        $asistencia = $this->service->update($id, $data);

        return redirect()->route('asistencias.show', $asistencia->id)->with('success', 'Asistencia actualizada.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('asistencias.index')->with('success', 'Asistencia eliminada.');
    }
}
