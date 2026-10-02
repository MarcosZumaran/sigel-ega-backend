<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiagieLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiagieLogController extends Controller
{
    public function __construct(
        private SiagieLogService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filtros = $request->only(['operacion', 'resultado', 'periodo_id', 'desde', 'hasta']);
        $perPage = (int) $request->query('per_page', 20);
        $perPage = min($perPage, 100);

        return response()->json($this->service->paginate($filtros, $perPage));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }
}
