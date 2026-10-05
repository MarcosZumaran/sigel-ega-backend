<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Area;
use App\Models\AsignacionDocente;
use App\Models\Seccion;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AsignacionDocenteService
{
    public function getAll(array $filters = []): Collection
    {
        $query = AsignacionDocente::with(['docente', 'seccion.grado', 'area']);
        if (! empty($filters['docente_id'])) {
            $query->where('docente_id', (int) $filters['docente_id']);
        }
        if (! empty($filters['seccion_id'])) {
            $query->where('seccion_id', (int) $filters['seccion_id']);
        }
        return $query->orderBy('seccion_id')->orderBy('area_id')->get();
    }

    public function getById(int $id): AsignacionDocente
    {
        $model = AsignacionDocente::with(['docente', 'seccion', 'area'])->find($id);
        if (! $model) {
            throw new NotFoundException('AsignacionDocente', $id);
        }
        return $model;
    }

    /**
     * Crea una asignación docente.
     * Reglas:
     * - Un docente no puede estar duplicado en la misma combinación (BD unique).
     * - Si es encargado (es_encargado=true) y area_id=null, no puede haber otro encargado en la misma sección.
     */
    public function create(array $data): AsignacionDocente
    {
        $this->validar($data);
        $this->assertEncargadoUnico($data);
        return AsignacionDocente::create($data);
    }

    public function update(int $id, array $data): AsignacionDocente
    {
        $model = $this->getById($id);
        $this->validar(array_merge($model->toArray(), array_filter($data, fn ($v) => ! is_null($v))));
        $this->assertEncargadoUnico(
            array_merge($model->toArray(), $data),
            $model->id
        );
        $model->update($data);
        return $model->refresh();
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);
        $model->delete();
    }

    public function restore(int $id): AsignacionDocente
    {
        $model = AsignacionDocente::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('AsignacionDocente', $id);
        }
        $model->restore();
        return $model->refresh();
    }

    private function validar(array $data): void
    {
        if (empty($data['docente_id'])) {
            throw ValidationException::withMessages(['docente_id' => 'El docente es obligatorio.']);
        }
        if (empty($data['seccion_id'])) {
            throw ValidationException::withMessages(['seccion_id' => 'La sección es obligatoria.']);
        }

        // Validar coherencia nivel sección ↔ área
        if (! empty($data['area_id'])) {
            $seccion = Seccion::with('grado.nivel')->find($data['seccion_id']);
            $area = Area::find($data['area_id']);

            if ($seccion && $area && $area->nivel_id) {
                $nivelSeccion = $seccion->grado?->nivel_id;
                if ($nivelSeccion && $area->nivel_id !== $nivelSeccion) {
                    throw ValidationException::withMessages([
                        'area_id' => 'El área no corresponde al nivel de la sección.',
                    ]);
                }
            }
        }
    }

    /**
     * Solo puede haber un encargado por sección (aunque el unique no lo bloquea
     * por el NULL de area_id).
     */
    private function assertEncargadoUnico(array $data, ?int $exceptoId = null): void
    {
        if (empty($data['es_encargado'])) return;

        $query = AsignacionDocente::query()
            ->where('seccion_id', (int) $data['seccion_id'])
            ->whereNull('area_id')
            ->where('es_encargado', true);

        if ($exceptoId) {
            $query->where('id', '!=', $exceptoId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'es_encargado' => 'Ya existe un docente encargado para esta sección.',
            ]);
        }
    }
}
