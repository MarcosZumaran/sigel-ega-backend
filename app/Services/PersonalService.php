<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Personal;
use Illuminate\Support\Collection;

class PersonalService
{
    public function getAll(): Collection
    {
        return Personal::with(['estado', 'usuario'])->orderBy('id')->get();
    }

    public function getById(int $id): Personal
    {
        $model = Personal::with(['estado', 'usuario', 'asistenciasPersonal'])->find($id);
        if (! $model) {
            throw new NotFoundException('Personal', $id);
        }

        return $model;
    }

    public function create(array $data): Personal
    {
        return Personal::create($data);
    }

    public function update(int $id, array $data): Personal
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);
        if ($model->asistenciasPersonal()->exists()) {
            throw new EnUsoException('No se puede eliminar el personal; tiene asistencias registradas.');
        }
        $model->delete();
    }

    public function restore(int $id): Personal
    {
        $model = Personal::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Personal eliminado', $id);
        }
        $model->restore();

        return $model->refresh();
    }

    public function getAsistencias(int $id): Collection
    {
        $model = $this->getById($id);

        return $model->asistenciasPersonal()->with(['archivos', 'registradoPor'])->orderBy('fecha', 'desc')->get();
    }
}
