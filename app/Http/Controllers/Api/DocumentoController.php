<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CorrelativoService;
use App\Services\DocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    public function __construct(
        private readonly DocumentoService $service,
        private readonly CorrelativoService $correlativos,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tipo_documento_id' => 'required|integer|exists:tipos_documento,id',
            'numero' => 'nullable|string|max:50',
            'asunto' => 'required|string|max:255',
            'destinatario' => 'nullable|string|max:200',
            'fecha' => 'nullable|date',
            'ruta_archivo' => 'nullable|string|max:500',
            'usuario_id' => 'nullable|integer|exists:users,id',
        ]);

        // RF-19: si no envían número, generarlo automáticamente del correlativo
        if (empty($data['numero'])) {
            $data['numero'] = $this->correlativos->siguiente(
                (int) $data['tipo_documento_id']
            );
        }

        $model = $this->service->create($data);

        return response()->json($model, 201);
    }

    /**
     * Previsualiza el próximo número correlativo sin consumirlo.
     */
    public function proximoNumero(Request $request): JsonResponse
    {
        $request->validate([
            'tipo_documento_id' => 'required|integer|exists:tipos_documento,id',
        ]);

        return response()->json([
            'proximo_numero' => $this->correlativos->proximo(
                (int) $request->tipo_documento_id
            ),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'tipo_documento_id' => 'sometimes|integer|exists:tipos_documento,id',
            'numero' => 'nullable|string|max:50',
            'asunto' => 'sometimes|string|max:255',
            'destinatario' => 'nullable|string|max:200',
            'fecha' => 'nullable|date',
            'ruta_archivo' => 'nullable|string|max:500',
            'usuario_id' => 'nullable|integer|exists:users,id',
        ]);

        $model = $this->service->update($id, $data);

        return response()->json($model);
    }

    public function restore(int $id): JsonResponse
    {
        return response()->json($this->service->restore($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Documento eliminado correctamente']);
    }
}
