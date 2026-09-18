<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\AsistenciaPersonal;
use App\Models\AsistenciaPersonalArchivo;
use App\Models\Personal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AsistenciaPersonalService
{
    public function getAll(): Collection
    {
        return AsistenciaPersonal::with(['personal', 'registradoPor', 'archivos'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function paginate(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $q = AsistenciaPersonal::with(['personal', 'registradoPor', 'archivos'])
            ->orderBy('fecha', 'desc');

        if (!empty($filtros['personal_id'])) {
            $q->where('personal_id', $filtros['personal_id']);
        }
        if (!empty($filtros['desde'])) {
            $q->where('fecha', '>=', $filtros['desde']);
        }
        if (!empty($filtros['hasta'])) {
            $q->where('fecha', '<=', $filtros['hasta']);
        }
        if (!empty($filtros['estado'])) {
            $q->where('estado', $filtros['estado']);
        }

        return $q->paginate($perPage);
    }

    public function getById(int $id): AsistenciaPersonal
    {
        $model = AsistenciaPersonal::with(['personal', 'registradoPor', 'archivos'])->find($id);
        if (! $model) {
            throw new NotFoundException('Asistencia de personal', $id);
        }

        return $model;
    }

    public function create(array $data): AsistenciaPersonal
    {
        return AsistenciaPersonal::create($data);
    }

    public function update(int $id, array $data): AsistenciaPersonal
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

    public function restore(int $id): AsistenciaPersonal
    {
        $model = AsistenciaPersonal::withTrashed()->with(['personal', 'archivos'])->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Asistencia de personal eliminada', $id);
        }
        $model->restore();

        return $model->refresh();
    }

    public function marcarPropia(int $authUserId, array $data): AsistenciaPersonal
    {
        $personal = Personal::where('user_id', $authUserId)->first();
        if (! $personal) {
            throw new NotFoundException('Personal vinculado al usuario', $authUserId);
        }
        $data['personal_id'] = $personal->id;
        $data['registrado_por'] = $authUserId;

        return AsistenciaPersonal::create($data);
    }

    public function justificar(int $id, ?string $descripcion, array $archivos = []): AsistenciaPersonal
    {
        $model = $this->getById($id);
        if ($descripcion !== null) {
            $model->descripcion_justificacion = $descripcion;
            $model->save();
        }
        foreach ($archivos as $file) {
            $path = $file->store('justificaciones_personal/' . $model->id, 'local');
            AsistenciaPersonalArchivo::create([
                'asistencia_id' => $model->id,
                'ruta_archivo' => $path,
                'nombre_original' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'tamano' => $file->getSize(),
            ]);
        }

        return $model->load(['archivos', 'personal', 'registradoPor']);
    }
}
