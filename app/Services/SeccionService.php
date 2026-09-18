<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Seccion;
use Illuminate\Support\Collection;

class SeccionService
{
    public function getAll(): Collection
    {
        return Seccion::with(['grado', 'docente'])->get();
    }

    public function getById(int $id): Seccion
    {
        $model = Seccion::with(['grado', 'docente'])->find($id);

        if (! $model) {
            throw new NotFoundException('Sección', $id);
        }

        return $model;
    }

    public function create(array $data): Seccion
    {
        return Seccion::create($data);
    }

    public function update(int $id, array $data): Seccion
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
                throw new EnUsoException('No se puede eliminar el Sección; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Seccion
    {
        $model = Seccion::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Seccion eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
