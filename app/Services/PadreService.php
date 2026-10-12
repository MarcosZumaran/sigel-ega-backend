<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Padre;
use Illuminate\Support\Collection;

class PadreService
{
    public function getAll(): Collection
    {
        return Padre::with(['estado', 'apoderado'])->get();
    }

    public function getById(int $id): Padre
    {
        $model = Padre::with(['estado', 'apoderado'])->find($id);

        if (! $model) {
            throw new NotFoundException('Padre de Familia', $id);
        }

        return $model;
    }

    public function create(array $data): Padre
    {
        // BUG-10 fix: el apoderado se crea cuando se vincula un padre a un
        // estudiante (desde la vista de Apoderados), no al crear el padre.
        unset($data['apoderado_id']);
        $data['apoderado_id'] = null;
        $padre = Padre::create($data);

        return $padre->load('apoderado');
    }

    public function update(int $id, array $data): Padre
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

    public function restore(int $id): Padre
    {
        $model = Padre::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Padre eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
