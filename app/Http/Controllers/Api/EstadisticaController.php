<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EstadisticaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstadisticaController extends Controller
{
    public function __construct(private readonly EstadisticaService $service) {}

    public function dashboard(): JsonResponse
    {
        return response()->json($this->service->getDashboard());
    }

    public function matriculasPorNivel(Request $request): JsonResponse
    {
        $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);

        return response()->json(
            $this->service->getMatriculasPorNivel($request->integer('periodo_id') ?: null)
        );
    }

    public function logrosCneb(Request $request): JsonResponse
    {
        $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
        ]);

        return response()->json(
            $this->service->getLogrosCneb(
                $request->integer('periodo_id') ?: null,
                $request->integer('seccion_id') ?: null
            )
        );
    }

    public function asistenciaMensual(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => 'required|integer|exists:periodos,id',
            'anio' => 'nullable|integer|min:2000|max:2100',
        ]);

        return response()->json(
            $this->service->getAsistenciaMensual($data['periodo_id'], $data['anio'] ?? null)
        );
    }

    public function ocupacionSecciones(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => 'required|integer|exists:periodos,id',
        ]);

        return response()->json($this->service->getOcupacionSecciones($data['periodo_id']));
    }
}
