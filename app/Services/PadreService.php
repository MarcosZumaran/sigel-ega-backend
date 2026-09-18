<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Apoderado;
use App\Models\Padre;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        // Flujo de secretaría: al registrar un padre se crea su apoderado (hub) automáticamente.
        // Se ignora cualquier apoderado_id enviado: siempre es un hub nuevo.
        unset($data['apoderado_id']);
        $apoderado = Apoderado::create(['uuid' => (string) Str::uuid()]);
        $data['apoderado_id'] = $apoderado->id;
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
