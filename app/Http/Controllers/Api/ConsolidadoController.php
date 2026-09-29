<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Bimestre;
use App\Models\ConclusionDescriptiva;
use App\Models\Matricula;
use App\Models\NivelLogroConsolidado;
use App\Services\EvaluacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConsolidadoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'seccion_id' => 'required|integer|exists:secciones,id',
            'bimestre_id' => 'required|integer|exists:bimestres,id',
            'periodo_id' => 'nullable|integer|exists:periodos,id',
        ]);

        $bimestre = Bimestre::query()->findOrFail($filters['bimestre_id']);
        $periodoId = $filters['periodo_id'] ?? $bimestre->periodo_id;

        $matriculas = Matricula::query()
            ->where('seccion_id', $filters['seccion_id'])
            ->where('periodo_id', $periodoId)
            ->with('estudiante')
            ->orderBy('id')
            ->get();

        $nivelId = $matriculas->first()?->seccion?->grado?->nivel_id;
        $areas = Area::query()->whereNull('area_padre_id')
            ->when($nivelId, fn ($q) => $q->where(fn ($w) => $w->where('nivel_id', $nivelId)->orWhereNull('nivel_id')))
            ->with(['areasHijas' => fn ($q) => $q->where('tipo', 'competencia')->orderBy('id')])
            ->orderBy('id')
            ->get();

        $consolidados = NivelLogroConsolidado::query()
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->where('bimestre_id', $bimestre->id)
            ->where('es_final', false)
            ->get()
            ->keyBy(fn ($c) => $c->matricula_id . '|' . $c->competencia_id);

        $conclusiones = ConclusionDescriptiva::query()
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->where('bimestre_id', $bimestre->id)
            ->get()
            ->keyBy(fn ($c) => $c->matricula_id . '|' . $c->competencia_id);

        $estudiantes = $matriculas->map(function ($mat) use ($areas, $bimestre, $consolidados, $conclusiones) {
            $niveles = [];
            foreach ($areas as $area) {
                foreach ($area->areasHijas as $comp) {
                    $key = $mat->id . '|' . $comp->id;
                    $niveles[] = [
                        'competencia_id' => $comp->id,
                        'propuesto' => EvaluacionService::calcularNivelLogroCompetencia($mat->id, $comp->id, $bimestre->id),
                        'guardado' => $consolidados->get($key)?->nivel,
                        'conclusion' => $conclusiones->get($key)?->texto,
                    ];
                }
            }

            return [
                'matricula_id' => $mat->id,
                'estudiante' => $mat->estudiante,
                'niveles' => $niveles,
            ];
        });

        return response()->json([
            'bimestre' => $bimestre,
            'areas' => $areas,
            'estudiantes' => $estudiantes,
        ]);
    }

    public function batch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bimestre_id' => 'required|integer|exists:bimestres,id',
            'items' => 'required|array|min:1',
            'items.*.matricula_id' => 'required|integer|exists:matriculas,id',
            'items.*.competencia_id' => 'required|integer|exists:areas,id',
            'items.*.bimestre_id' => 'nullable|integer|exists:bimestres,id',
            'items.*.nivel' => 'required|string|in:AD,A,B,C',
            'items.*.conclusion' => 'nullable|string|max:2000',
        ]);

        $bimestre = Bimestre::query()->findOrFail($data['bimestre_id']);
        if (! $bimestre->activo) {
            throw new HttpException(403, 'El bimestre está cerrado. No se pueden modificar calificaciones.');
        }

        $errores = [];
        $guardados = 0;

        DB::transaction(function () use ($data, $bimestre, &$errores, &$guardados) {
            foreach ($data['items'] as $i => $item) {
                if (isset($item['bimestre_id']) && (int) $item['bimestre_id'] !== $bimestre->id) {
                    $errores[] = "Ítem {$i}: bimestre_id no coincide con el lote.";
                    continue;
                }
                if ($item['nivel'] === 'C' && trim($item['conclusion'] ?? '') === '') {
                    $errores[] = "Ítem {$i}: el nivel C exige conclusión descriptiva.";
                    continue;
                }

                $competencia = Area::query()->find($item['competencia_id']);
                NivelLogroConsolidado::query()->updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'competencia_id' => $item['competencia_id'],
                        'bimestre_id' => $bimestre->id,
                        'es_final' => false,
                    ],
                    [
                        'area_id' => $competencia?->area_padre_id,
                        'nivel' => $item['nivel'],
                    ]
                );

                if (trim($item['conclusion'] ?? '') !== '') {
                    ConclusionDescriptiva::query()->updateOrCreate(
                        [
                            'matricula_id' => $item['matricula_id'],
                            'competencia_id' => $item['competencia_id'],
                            'bimestre_id' => $bimestre->id,
                        ],
                        ['texto' => $item['conclusion']]
                    );
                }
                $guardados++;
            }

            if ($errores !== []) {
                throw ValidationException::withMessages(['items' => $errores]);
            }
        });

        return response()->json(['guardados' => $guardados, 'errores' => []]);
    }
}
