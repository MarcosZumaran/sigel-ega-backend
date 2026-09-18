<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function __construct(private readonly AuditoriaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'tabla' => 'nullable|string|max:50',
            'accion' => 'nullable|in:create,update,delete,restore,login,logout',
            'usuario_id' => 'nullable|integer|exists:users,id',
            'registro_id' => 'nullable|integer',
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
}
