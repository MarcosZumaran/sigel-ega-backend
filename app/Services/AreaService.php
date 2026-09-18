<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Area;
use Illuminate\Support\Collection;

class AreaService
{
    public function getAll(): Collection
    {
        return Area::with(['areaPadre'])->get();
    }

    public function getById(int $id): Area
    {
        $model = Area::with(['areaPadre'])->find($id);

        if (! $model) {
            throw new NotFoundException('Área', $id);
        }

        return $model;
    }

    public function create(array $data): Area
    {
        return Area::create($data);
    }

    public function update(int $id, array $data): Area
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['areasHijas', 'calificaciones'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Área; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Area
    {
        $model = Area::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Area eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
