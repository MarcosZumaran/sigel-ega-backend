<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Collection;

class UserService
{
    public function getAll(): Collection
    {
        return User::with(['rol', 'estado'])->get();
    }

    public function getById(int $id): User
    {
        $model = User::with(['rol', 'estado'])->find($id);

        if (! $model) {
            throw new NotFoundException('Usuario', $id);
        }

        return $model;
    }

    public function create(array $data): User
    {
        $this->assertAdminParaRolAdmin($data);
        return User::create($data);
    }

    public function update(int $id, array $data): User
    {
        $this->assertAdminParaRolAdmin($data);
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    private function assertAdminParaRolAdmin(array $data): void
    {
        if (! isset($data['rol_id']) || $data['rol_id'] === null) return;
        if ((int) $data['rol_id'] === 1) {
            $user = auth()->user();
            if (! $user || ! $user->hasRole('ADMIN')) {
                throw new EnUsoException('Solo el administrador puede asignar rol ADMIN.');
            }
        }
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['documentos'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Usuario; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): User
    {
        $model = User::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('User eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
