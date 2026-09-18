<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Documento;
use Illuminate\Support\Collection;

class DocumentoService
{
    public function getAll(): Collection
    {
        return Documento::with(['tipoDocumento', 'usuario'])->get();
    }

    public function getById(int $id): Documento
    {
        $model = Documento::with(['tipoDocumento', 'usuario'])->find($id);

        if (! $model) {
            throw new NotFoundException('Documento', $id);
        }

        return $model;
    }

    public function create(array $data): Documento
    {
        return Documento::create($data);
    }

    public function update(int $id, array $data): Documento
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

    public function restore(int $id): Documento
    {
        $model = Documento::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Documento eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
