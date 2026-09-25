<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Padre;
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

    public function attachPadre(int $id, int $padreId): Padre
    {
        $this->getById($id);
        $padre = Padre::find($padreId);
        if (! $padre) {
            throw new NotFoundException('Padre de Familia', $padreId);
        }
        $padre->apoderado_id = $id;
        $padre->save();

        return $padre->refresh();
    }

    public function detachPadre(int $id, int $padreId): void
    {
        $this->getById($id);
        Padre::whereKey($padreId)->where('apoderado_id', $id)->update(['apoderado_id' => null]);
    }

    public function attachEstudiante(int $id, int $estudianteId): Estudiante
    {
        $this->getById($id);
        $estudiante = Estudiante::find($estudianteId);
        if (! $estudiante) {
            throw new NotFoundException('Estudiante', $estudianteId);
        }
        $estudiante->apoderado_id = $id;
        $estudiante->save();

        return $estudiante->refresh();
    }

    public function detachEstudiante(int $id, int $estudianteId): void
    {
        $this->getById($id);
        Estudiante::whereKey($estudianteId)->where('apoderado_id', $id)->update(['apoderado_id' => null]);
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
