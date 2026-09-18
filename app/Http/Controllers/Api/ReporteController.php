<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReporteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function __construct(private readonly ReporteService $service) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'tipo' => 'nullable|string|max:50',
            'periodo_id' => 'nullable|integer|exists:periodos,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
            'formato' => 'nullable|in:pdf,excel,csv',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json($this->service->paginate($request));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tipo' => 'required|string|max:50',
            'periodo_id' => 'nullable|integer|exists:periodos,id',
            'seccion_id' => 'nullable|integer|exists:secciones,id',
            'formato' => 'nullable|in:pdf,excel,csv',
            'parametros' => 'nullable|array',
        ]);
        $data['generado_por'] = $request->user()?->id;

        return response()->json($this->service->generar($data), 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Reporte eliminado correctamente.']);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }
}
