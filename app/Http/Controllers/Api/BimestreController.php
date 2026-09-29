<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bimestre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BimestreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);
        $q = Bimestre::with('periodo')->orderBy('numero');
        if (! empty($data['periodo_id'])) {
            $q->where('periodo_id', (int) $data['periodo_id']);
        }
        return response()->json($q->get());
    }

    public function activo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);
        $q = Bimestre::with('periodo')->where('activo', true);
        if (! empty($data['periodo_id'])) {
            $q->where('periodo_id', (int) $data['periodo_id']);
        }
        return response()->json($q->orderBy('numero')->first());
    }

    public function activar(int $id): JsonResponse
    {
        $bimestre = Bimestre::findOrFail($id);
        Bimestre::where('periodo_id', $bimestre->periodo_id)->update(['activo' => false]);
        $bimestre->update(['activo' => true]);
        return response()->json($bimestre->fresh());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string|max:80',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'activo' => 'nullable|boolean',
        ]);

        $bimestre = Bimestre::findOrFail($id);

        $inicio = $data['fecha_inicio'] ?? $bimestre->fecha_inicio;
        $fin = $data['fecha_fin'] ?? $bimestre->fecha_fin;
        if ($inicio && $fin && $fin <= $inicio) {
            return response()->json(['message' => 'La fecha de fin debe ser posterior a la fecha de inicio.', 'errors' => ['fecha_fin' => ['La fecha de fin debe ser posterior a la fecha de inicio.']]], 422);
        }

        $bimestre->update($data);

        return response()->json($bimestre->fresh());
    }
}
