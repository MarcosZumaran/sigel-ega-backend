<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Docente;
use Illuminate\Support\Collection;

class DocenteService
{
    public function getAll(): Collection
    {
        return Docente::orderBy('id')->get();
    }

    public function getById(int $id): Docente
    {
        $model = Docente::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Docente', $id);
        }

        return $model;
    }

    public function create(array $data): Docente
    {
        return Docente::create($data);
    }

    public function update(int $id, array $data): Docente
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
                throw new EnUsoException('No se puede eliminar el Docente; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Docente
    {
        $model = Docente::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Docente eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
