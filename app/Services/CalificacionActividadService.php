<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Actividad;
use App\Models\Bimestre;
use App\Models\CalificacionActividad;
use App\Models\Matricula;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CalificacionActividadService
{
    /**
     * Lista calificaciones de una actividad.
     */
    public function porActividad(int $actividadId): array
    {
        $actividad = Actividad::with(['competencia', 'bimestre', 'seccion'])->findOrFail($actividadId);
        $matriculas = Matricula::with('estudiante')
            ->where('seccion_id', $actividad->seccion_id)
            ->where('periodo_id', $actividad->bimestre->periodo_id)
            ->get();

        $calificaciones = CalificacionActividad::query()
            ->where('actividad_id', $actividadId)
            ->get()
            ->keyBy('matricula_id');

        $filas = $matriculas->map(fn ($mat) => [
            'matricula_id' => $mat->id,
            'estudiante' => $mat->estudiante,
            'nivel_logro' => $calificaciones->get($mat->id)?->nivel_logro,
        ]);

        return [
            'actividad' => $actividad,
            'calificaciones' => $filas,
        ];
    }

    /**
     * Guarda en lote los niveles de todos los alumnos de una actividad.
     */
    public function guardarBatch(int $actividadId, array $items): array
    {
        $actividad = Actividad::with('bimestre')->findOrFail($actividadId);
        $this->assertBimestreAbierto((int) $actividad->bimestre_id);

        $guardados = 0;
        $errores = [];

        DB::transaction(function () use ($actividadId, $items, &$guardados, &$errores) {
            foreach ($items as $i => $item) {
                if (empty($item['matricula_id'])) {
                    $errores[] = "Ítem {$i}: matricula_id requerido.";
                    continue;
                }
                $nivel = strtoupper(trim((string) ($item['nivel_logro'] ?? '')));
                if (! in_array($nivel, ['AD', 'A', 'B', 'C'], true)) {
                    $errores[] = "Ítem {$i}: nivel_logro debe ser AD, A, B o C.";
                    continue;
                }

                CalificacionActividad::updateOrCreate(
                    [
                        'actividad_id' => $actividadId,
                        'matricula_id' => (int) $item['matricula_id'],
                    ],
                    ['nivel_logro' => $nivel]
                );
                $guardados++;
            }

            if ($errores !== []) {
                throw ValidationException::withMessages(['items' => $errores]);
            }
        });

        return ['guardados' => $guardados, 'errores' => []];
    }

    public function delete(int $id): void
    {
        $model = CalificacionActividad::find($id);
        if (! $model) {
            throw new NotFoundException('CalificacionActividad', $id);
        }
        $model->delete();
    }

    public function restore(int $id): CalificacionActividad
    {
        $model = CalificacionActividad::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('CalificacionActividad', $id);
        }
        $model->restore();
        return $model->refresh();
    }

    private function assertBimestreAbierto(int $bimestreId): void
    {
        $bim = Bimestre::find($bimestreId);
        if ($bim && ! (bool) $bim->activo) {
            throw new HttpException(403, 'El bimestre está cerrado. No se pueden modificar calificaciones de actividades.');
        }
    }
}
