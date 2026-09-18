<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Periodo;
use Illuminate\Support\Collection;

class PeriodoService
{
    public function getAll(): Collection
    {
        return Periodo::orderBy('id')->get();
    }

    public function getById(int $id): Periodo
    {
        $model = Periodo::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Periodo', $id);
        }

        return $model;
    }

    public function create(array $data): Periodo
    {
        return Periodo::create($data);
    }

    public function update(int $id, array $data): Periodo
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['matriculas'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Periodo; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Periodo
    {
        $model = Periodo::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Periodo eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
