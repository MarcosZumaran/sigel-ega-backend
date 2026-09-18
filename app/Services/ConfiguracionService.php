<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Configuracion;
use Illuminate\Support\Collection;

class ConfiguracionService
{
    public function getAll(): Collection
    {
        return Configuracion::orderBy('id')->get();
    }

    public function getById(int $id): Configuracion
    {
        $model = Configuracion::find($id);

        if (! $model) {
            throw new NotFoundException('Configuración', $id);
        }

        return $model;
    }

    public function create(array $data): Configuracion
    {
        return Configuracion::create($data);
    }

    public function update(int $id, array $data): Configuracion
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);
        $model->delete();
    }

    public function restore(int $id): Configuracion
    {
        $model = Configuracion::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Configuracion eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
