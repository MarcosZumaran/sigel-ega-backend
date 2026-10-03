<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Services\EstudianteService;
use App\Services\EvaluacionService;
use App\Services\FumService;
use App\Services\WordExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    public function __construct(private readonly EstudianteService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo_estudiante' => 'nullable|string|max:30|unique:estudiantes,codigo_estudiante',
            'dni' => 'nullable|string|size:8|unique:estudiantes,dni',
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'lengua_materna' => 'nullable|string|max:50',
            'autoidentificacion_etnica' => 'nullable|string|max:100',
            'tiene_discapacidad' => 'nullable|boolean',
            'tipo_discapacidad' => 'nullable|string|max:100',
            'grado_discapacidad' => 'nullable|in:Leve,Moderada,Severa',
            'tiene_certificado_discapacidad' => 'nullable|boolean',
            'pais_nacimiento' => 'nullable|string|max:50',
            'departamento_nacimiento' => 'nullable|string|max:100',
            'provincia_nacimiento' => 'nullable|string|max:100',
            'distrito_nacimiento' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'nivel_id' => 'nullable|integer|exists:niveles,id',
            'grado_id' => 'nullable|integer|exists:grados,id',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'apoderado_id' => 'nullable|integer|exists:apoderados,id',
            'padre_id' => 'nullable|integer|exists:padres,id',
        ]);

        // El servicio hereda el apoderado_id del padre seleccionado (padre_id).
        $model = $this->service->create($data);

        return response()->json($model, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function informeProgreso(Request $request, int $id, EvaluacionService $evaluacion): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);

        return response()->json($evaluacion->generarDatosInformeProgreso($id, $data['periodo_id'] ?? null));
    }

    public function informeProgresoPdf(Request $request, int $id, EvaluacionService $evaluacion)
    {
        $data = $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);

        $informe = $evaluacion->generarDatosInformeProgreso($id, $data['periodo_id'] ?? null);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.boleta', ['informe' => $informe])
            ->setPaper('a4')
            ->download("informe-progreso-{$id}.pdf");
    }

    /**
     * Retorna los datos JSON de la FUM (para previsualización en frontend).
     */
    public function fum(Request $request, int $id, FumService $fum): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);

        $estudiante = Estudiante::with(['nivel', 'grado', 'estado', 'apoderado'])
            ->findOrFail($id);

        return response()->json($fum->datos($estudiante, $data['periodo_id'] ?? null));
    }

    /**
     * Genera el PDF oficial de la FUM.
     */
    public function fumPdf(Request $request, int $id, FumService $fum)
    {
        $data = $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);

        $estudiante = Estudiante::with(['nivel', 'grado', 'estado', 'apoderado'])
            ->findOrFail($id);

        $datos = $fum->datos($estudiante, $data['periodo_id'] ?? null);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.fum', $datos)
            ->setPaper('a4', 'portrait');

        $nombre = 'FUM_'.($estudiante->dni ?: $estudiante->id).'_'.date('Ymd').'.pdf';

        return $pdf->download($nombre);
    }

    /**
     * Genera el documento Word oficial de la FUM.
     */
    public function fumWord(Request $request, int $id, FumService $fum)
    {
        $request->validate([
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);

        $estudiante = Estudiante::with(['nivel', 'grado', 'estado', 'apoderado'])
            ->findOrFail($id);

        $rutaTemp = $fum->generarDocx($estudiante, $request->query('periodo_id') ? (int) $request->query('periodo_id') : null);

        $nombre = 'FUM_'.($estudiante->dni ?: $estudiante->id).'_'.date('Ymd').'.docx';

        return response()->download($rutaTemp, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'codigo_estudiante' => 'nullable|string|max:30|unique:estudiantes,codigo_estudiante,$id,id',
            'dni' => 'nullable|string|size:8|unique:estudiantes,dni,$id,id',
            'nombres' => 'sometimes|string|max:150',
            'apellidos' => 'sometimes|string|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'lengua_materna' => 'nullable|string|max:50',
            'autoidentificacion_etnica' => 'nullable|string|max:100',
            'tiene_discapacidad' => 'nullable|boolean',
            'tipo_discapacidad' => 'nullable|string|max:100',
            'grado_discapacidad' => 'nullable|in:Leve,Moderada,Severa',
            'tiene_certificado_discapacidad' => 'nullable|boolean',
            'pais_nacimiento' => 'nullable|string|max:50',
            'departamento_nacimiento' => 'nullable|string|max:100',
            'provincia_nacimiento' => 'nullable|string|max:100',
            'distrito_nacimiento' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
            'nivel_id' => 'nullable|integer|exists:niveles,id',
            'grado_id' => 'nullable|integer|exists:grados,id',
            'estado_id' => 'nullable|integer|exists:estados,id',
            'apoderado_id' => 'nullable|integer|exists:apoderados,id',
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

        return response()->json(['message' => 'Estudiante eliminado correctamente']);
    }
}
