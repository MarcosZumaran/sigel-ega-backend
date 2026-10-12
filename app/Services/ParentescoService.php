<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Parentesco;
use Illuminate\Support\Collection;

class ParentescoService
{
    public function getAll(): Collection
    {
        return Parentesco::orderBy('id')->get();
    }

    public function getById(int $id): Parentesco
    {
        $model = Parentesco::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Parentesco', $id);
        }

        return $model;
    }

    public function create(array $data): Parentesco
    {
        return Parentesco::create($data);
    }

    public function update(int $id, array $data): Parentesco
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        // Parentesco ya no tiene pivote (ver migración
        // 2026_08_20_145102_drop_padre_estudiante_table.php); borrado directo.
        $model->delete();
    }

    public function restore(int $id): Parentesco
    {
        $model = Parentesco::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Parentesco eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
