<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\TipoEvaluacion;
use Illuminate\Support\Collection;

class TipoEvaluacionService
{
    public function getAll(): Collection
    {
        return TipoEvaluacion::orderBy('id')->get();
    }

    public function getById(int $id): TipoEvaluacion
    {
        $model = TipoEvaluacion::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Tipo de Evaluación', $id);
        }

        return $model;
    }

    public function create(array $data): TipoEvaluacion
    {
        return TipoEvaluacion::create($data);
    }

    public function update(int $id, array $data): TipoEvaluacion
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['calificaciones'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Tipo de Evaluación; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): TipoEvaluacion
    {
        $model = TipoEvaluacion::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('TipoEvaluacion eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
