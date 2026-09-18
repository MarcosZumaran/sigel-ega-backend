<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Apoderado;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ApoderadoService
{
    /**
     * Hub sin datos personales: devuelve el nodo con conteos y relaciones.
     */
    public function getAll(): Collection
    {
        return Apoderado::with(['padres', 'estudiantes'])
            ->withCount(['padres', 'estudiantes'])
            ->orderByDesc('id')
            ->get();
    }

    public function getById(int $id): Apoderado
    {
        $model = Apoderado::with(['padres', 'estudiantes'])
            ->withCount(['padres', 'estudiantes'])
            ->find($id);

        if (! $model) {
            throw new NotFoundException('Apoderado', $id);
        }

        return $model;
    }

    public function create(array $data): Apoderado
    {
        return Apoderado::create([
            'uuid' => $data['uuid'] ?? (string) Str::uuid(),
        ]);
    }

    public function update(int $id, array $data): Apoderado
    {
        $model = $this->getById($id);

        if (array_key_exists('uuid', $data)) {
            $model->update(['uuid' => $data['uuid']]);
        }

        return $model->refresh();
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        if ($model->padres()->exists() || $model->estudiantes()->exists()) {
            throw new EnUsoException('No se puede eliminar el Apoderado; tiene padres o estudiantes asociados.');
        }

        $model->delete();
    }

    public function getPadres(int $id): Collection
    {
        return $this->getById($id)->padres;
    }

    public function getEstudiantes(int $id): Collection
    {
        return $this->getById($id)->estudiantes;
    }

    public function restore(int $id): Apoderado
    {
        $model = Apoderado::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Apoderado eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
