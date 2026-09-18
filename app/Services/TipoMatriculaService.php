<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\TipoMatricula;
use Illuminate\Support\Collection;

class TipoMatriculaService
{
    public function getAll(): Collection
    {
        return TipoMatricula::orderBy('id')->get();
    }

    public function getById(int $id): TipoMatricula
    {
        $model = TipoMatricula::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Tipo de Matrícula', $id);
        }

        return $model;
    }

    public function create(array $data): TipoMatricula
    {
        return TipoMatricula::create($data);
    }

    public function update(int $id, array $data): TipoMatricula
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['matriculas'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Tipo de Matrícula; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): TipoMatricula
    {
        $model = TipoMatricula::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('TipoMatricula eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
