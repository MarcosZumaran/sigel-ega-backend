<?php

namespace App\Http\Controllers;

use App\Models\Padre;
use App\Services\PadreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PadreWebController extends Controller
{
    public function __construct(private readonly PadreService $service)
    {
    }

    public function index(Request $request): Response
    {
        $q = Padre::with(['estado', 'apoderado'])->orderBy('apellidos')->orderBy('nombres');

        if ($request->filled('buscar')) {
            $b = trim($request->input('buscar'));
            $q->where(function ($w) use ($b) {
                $w->where('dni', 'like', "%{$b}%")
                    ->orWhere('nombres', 'like', "%{$b}%")
                    ->orWhere('apellidos', 'like', "%{$b}%");
            });
        }

        return Inertia::render('Padres/Index', [
            'padres' => $q->paginate(15)->withQueryString(),
            'filtros' => $request->only('buscar'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Padres/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dni' => 'required|string|size:8|unique:padres,dni',
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'ocupacion' => 'nullable|string|max:100',
            'estado_id' => 'nullable|integer|exists:estados,id',
        ]);

        // El servicio crea el apoderado (hub) automáticamente.
        $padre = $this->service->create($data);

        return redirect()->route('padres.show', $padre->id)
            ->with('success', 'Padre creado y apoderado #' . $padre->apoderado_id . ' asignado automáticamente.');
    }

    public function show(int $id): Response
    {
        $padre = Padre::with(['estado', 'apoderado.padres', 'apoderado.estudiantes'])->findOrFail($id);

        return Inertia::render('Padres/Show', ['padre' => $padre]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Padres/Edit', ['padre' => $this->service->getById($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'dni' => 'sometimes|string|size:8|unique:padres,dni,' . $id . ',id',
            'nombres' => 'sometimes|string|max:150',
            'apellidos' => 'sometimes|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'ocupacion' => 'nullable|string|max:100',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'apoderado_id' => 'nullable|integer|exists:apoderados,id',
        ]);

        $padre = $this->service->update($id, $data);

        return redirect()->route('padres.show', $padre->id)->with('success', 'Padre actualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('padres.index')->with('success', 'Padre eliminado.');
    }

    public function restore(int $id): RedirectResponse
    {
        $padre = $this->service->restore($id);

        return redirect()->route('padres.show', $padre->id)->with('success', 'Padre restaurado.');
    }
}
