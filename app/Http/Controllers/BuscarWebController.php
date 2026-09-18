<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Padre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuscarWebController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));

        return Inertia::render('Buscar/Index', [
            'q' => $q,
            'estudiantes' => $q !== '' ? $this->buscarEstudiantes($q, 10) : [],
            'padres' => $q !== '' ? $this->buscarPadres($q, 10) : [],
            'matriculas' => $q !== '' ? $this->buscarMatriculas($q, 10) : [],
            'documentos' => $q !== '' ? $this->buscarDocumentos($q, 10) : [],
        ]);
    }

    public function estudiantes(Request $request): JsonResponse
    {
        return response()->json($this->buscarEstudiantes(trim((string) $request->input('q', '')), 8));
    }

    public function padres(Request $request): JsonResponse
    {
        return response()->json($this->buscarPadres(trim((string) $request->input('q', '')), 8));
    }

    public function vacantesSeccion(int $id): JsonResponse
    {
        $seccion = \App\Models\Seccion::findOrFail($id);
        $ocupadas = Matricula::where('seccion_id', $id)->count();
        $vacantes = (int) ($seccion->vacantes ?? 0);

        return response()->json([
            'vacantes' => $vacantes,
            'ocupadas' => $ocupadas,
            'disponibles' => max(0, $vacantes - $ocupadas),
        ]);
    }

    /** @return array<int, array> */
    private function buscarEstudiantes(string $q, int $limit): array
    {
        if (mb_strlen($q) < 2) return [];

        return Estudiante::query()
            ->where('dni', 'like', "%{$q}%")
            ->orWhere('nombres', 'like', "%{$q}%")
            ->orWhere('apellidos', 'like', "%{$q}%")
            ->orWhere('codigo_estudiante', 'like', "%{$q}%")
            ->with(['apoderado:id,uuid'])
            ->limit($limit)->get()
            ->map(fn (Estudiante $e) => [
                'id' => $e->id,
                'dni' => $e->dni,
                'nombres' => $e->nombres,
                'apellidos' => $e->apellidos,
                'apoderado_id' => $e->apoderado_id,
            ])->all();
    }

    /** @return array<int, array> */
    private function buscarPadres(string $q, int $limit): array
    {
        if (mb_strlen($q) < 2) return [];

        return Padre::query()
            ->where('dni', 'like', "%{$q}%")
            ->orWhere('nombres', 'like', "%{$q}%")
            ->orWhere('apellidos', 'like', "%{$q}%")
            ->with(['apoderado:id,uuid'])
            ->limit($limit)->get()
            ->map(fn (Padre $p) => [
                'id' => $p->id,
                'dni' => $p->dni,
                'nombres' => $p->nombres,
                'apellidos' => $p->apellidos,
                'apoderado_id' => $p->apoderado_id,
            ])->all();
    }

    /** @return array<int, array> */
    private function buscarMatriculas(string $q, int $limit): array
    {
        if (mb_strlen($q) < 2) return [];

        return Matricula::query()
            ->whereHas('estudiante', fn ($w) => $w
                ->where('dni', 'like', "%{$q}%")
                ->orWhere('nombres', 'like', "%{$q}%")
                ->orWhere('apellidos', 'like', "%{$q}%"))
            ->with(['estudiante:id,nombres,apellidos', 'seccion:id,nombre', 'periodo:id,nombre'])
            ->limit($limit)->get()
            ->map(fn (Matricula $m) => [
                'id' => $m->id,
                'estudiante' => trim(($m->estudiante->nombres ?? '').' '.($m->estudiante->apellidos ?? '')),
                'seccion' => $m->seccion->nombre ?? '—',
                'periodo' => $m->periodo->nombre ?? '—',
            ])->all();
    }

    /** @return array<int, array> */
    private function buscarDocumentos(string $q, int $limit): array
    {
        if (mb_strlen($q) < 2) return [];

        return Documento::query()
            ->where('numero', 'like', "%{$q}%")
            ->orWhere('asunto', 'like', "%{$q}%")
            ->orWhere('destinatario', 'like', "%{$q}%")
            ->limit($limit)->get(['id', 'numero', 'asunto'])
            ->toArray();
    }
}
