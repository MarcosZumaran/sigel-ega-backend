<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Grado;
use Illuminate\Support\Collection;

class GradoService
{
    public function getAll(): Collection
    {
        return Grado::with(['nivel'])->get();
    }

    public function getById(int $id): Grado
    {
        $model = Grado::with(['nivel'])->find($id);

        if (! $model) {
            throw new NotFoundException('Grado', $id);
        }

        return $model;
    }

    public function create(array $data): Grado
    {
        return Grado::create($data);
    }

    public function update(int $id, array $data): Grado
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['secciones'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Grado; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Grado
    {
        $model = Grado::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Grado eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
