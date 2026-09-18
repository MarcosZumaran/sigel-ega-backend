<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Estado;
use Illuminate\Support\Collection;

class EstadoService
{
    public function getAll(): Collection
    {
        return Estado::orderBy('id')->get();
    }

    public function getById(int $id): Estado
    {
        $model = Estado::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Estado', $id);
        }

        return $model;
    }

    public function create(array $data): Estado
    {
        return Estado::create($data);
    }

    public function update(int $id, array $data): Estado
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
                throw new EnUsoException('No se puede eliminar el Estado; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Estado
    {
        $model = Estado::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Estado eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
