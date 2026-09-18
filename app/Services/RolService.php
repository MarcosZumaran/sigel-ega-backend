<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Rol;
use Illuminate\Support\Collection;

class RolService
{
    public function getAll(): Collection
    {
        return Rol::orderBy('id')->get();
    }

    public function getById(int $id): Rol
    {
        $model = Rol::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Rol', $id);
        }

        return $model;
    }

    public function create(array $data): Rol
    {
        return Rol::create($data);
    }

    public function update(int $id, array $data): Rol
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['users'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Rol; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Rol
    {
        $model = Rol::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Rol eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
