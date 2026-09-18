<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Padre;
use App\Services\ApoderadoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ApoderadoWebController extends Controller
{
    public function __construct(private readonly ApoderadoService $service)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Apoderados', [
            'apoderados' => $this->service->getAll(),
            'padres' => Padre::select(['id', 'dni', 'nombres', 'apellidos', 'apoderado_id'])
                ->orderBy('apellidos')->orderBy('nombres')->get(),
            'estudiantes' => Estudiante::select(['id', 'dni', 'nombres', 'apellidos', 'apoderado_id'])
                ->orderBy('apellidos')->orderBy('nombres')->get(),
        ]);
    }

    public function show(int $id): Response
    {
        $apoderado = $this->service->getById($id);

        return Inertia::render('Apoderados/Show', [
            'apoderado' => $apoderado,
            'padres' => Padre::select(['id', 'dni', 'nombres', 'apellidos', 'apoderado_id'])
                ->orderBy('apellidos')->orderBy('nombres')->get(),
            'estudiantes' => Estudiante::select(['id', 'dni', 'nombres', 'apellidos', 'apoderado_id'])
                ->orderBy('apellidos')->orderBy('nombres')->get(),
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return Redirect::route('apoderados.index')->with('success', 'Apoderado eliminado');
    }

    public function attachPadre(int $id): RedirectResponse
    {
        $data = request()->validate([
            'padre_id' => 'required|integer|exists:padres,id',
        ]);

        $this->service->getById($id);
        Padre::whereKey($data['padre_id'])->update(['apoderado_id' => $id]);

        return Redirect::back()->with('success', 'Padre asignado al apoderado');
    }

    public function detachPadre(int $id, int $padreId): RedirectResponse
    {
        $this->service->getById($id);
        Padre::whereKey($padreId)->where('apoderado_id', $id)->update(['apoderado_id' => null]);

        return Redirect::back()->with('success', 'Padre desvinculado');
    }

    public function attachEstudiante(int $id): RedirectResponse
    {
        $data = request()->validate([
            'estudiante_id' => 'required|integer|exists:estudiantes,id',
        ]);

        $this->service->getById($id);
        Estudiante::whereKey($data['estudiante_id'])->update(['apoderado_id' => $id]);

        return Redirect::back()->with('success', 'Estudiante asignado al apoderado');
    }

    public function detachEstudiante(int $id, int $estudianteId): RedirectResponse
    {
        $this->service->getById($id);
        Estudiante::whereKey($estudianteId)->where('apoderado_id', $id)->update(['apoderado_id' => null]);

        return Redirect::back()->with('success', 'Estudiante desvinculado');
    }
}
