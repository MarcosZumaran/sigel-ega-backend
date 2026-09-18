<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Nivel;
use Illuminate\Support\Collection;

class NivelService
{
    public function getAll(): Collection
    {
        return Nivel::orderBy('id')->get();
    }

    public function getById(int $id): Nivel
    {
        $model = Nivel::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Nivel', $id);
        }

        return $model;
    }

    public function create(array $data): Nivel
    {
        return Nivel::create($data);
    }

    public function update(int $id, array $data): Nivel
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['grados'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Nivel; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Nivel
    {
        $model = Nivel::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Nivel eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
