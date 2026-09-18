<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\TipoDocumento;
use Illuminate\Support\Collection;

class TipoDocumentoService
{
    public function getAll(): Collection
    {
        return TipoDocumento::orderBy('id')->get();
    }

    public function getById(int $id): TipoDocumento
    {
        $model = TipoDocumento::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Tipo de Documento', $id);
        }

        return $model;
    }

    public function create(array $data): TipoDocumento
    {
        return TipoDocumento::create($data);
    }

    public function update(int $id, array $data): TipoDocumento
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['documentos'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Tipo de Documento; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): TipoDocumento
    {
        $model = TipoDocumento::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('TipoDocumento eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
