<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Asistencia;
use App\Models\Matricula;
use App\Models\Personal;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AsistenciaService
{
    public function getAll(): Collection
    {
        return Asistencia::with(['matricula'])->get();
    }

    public function getById(int $id): Asistencia
    {
        $model = Asistencia::with(['matricula'])->find($id);

        if (! $model) {
            throw new NotFoundException('Asistencia', $id);
        }

        return $model;
    }

    public function create(array $data): Asistencia
    {
        $this->validar($data);
        $this->assertPuedeGestionarAsistencia($data);
        return Asistencia::create($data);
    }

    public function update(int $id, array $data): Asistencia
    {
        $model = $this->getById($id);
        $fullData = array_merge($model->toArray(), array_filter($data, fn ($v) => ! is_null($v)));
        $this->validar($fullData);
        $this->assertPuedeGestionarAsistencia($fullData, $model);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);
        $this->assertPuedeGestionarAsistencia($model->toArray(), $model);
        $model->delete();
    }

    private function assertPuedeGestionarAsistencia(array $data, ?Asistencia $existing = null): void
    {
        $user = auth()->user();
        if (! $user) return;
        if ((int) $user->rol_id === 1) return;
        $matId = $data['matricula_id'] ?? $existing?->matricula_id;
        if (! $matId) throw new EnUsoException('Solo el administrador puede gestionar asistencias sin matrícula.');
        $mat = Matricula::with('seccion')->find($matId);
        if (! $mat || ! $mat->seccion) throw new EnUsoException('Matrícula o sección no encontrada.');
        $docenteId = $mat->seccion->docente_id;
        $personal = Personal::where('user_id', $user->id)->first();
        $ok = false;
        if ($personal && $personal->categoria === 'docente' && (int) $personal->id === (int) $docenteId) $ok = true;
        if (! $ok) throw new EnUsoException('Solo el docente de la sección o el administrador puede gestionar asistencias de esa matrícula.');
    }

    private function validar(array $data): void
    {
        $matId = $data['matricula_id'] ?? null;
        $fecha = $data['fecha'] ?? null;
        if ($matId && $fecha) {
            $mat = Matricula::with('periodo')->find($matId);
            if ($mat && $mat->periodo && $mat->periodo->fecha_inicio && $mat->periodo->fecha_fin) {
                if ($fecha < $mat->periodo->fecha_inicio || $fecha > $mat->periodo->fecha_fin) {
                    throw ValidationException::withMessages(['fecha' => 'La fecha de asistencia debe estar dentro del periodo de la matrícula ('.$mat->periodo->fecha_inicio.' a '.$mat->periodo->fecha_fin.').']);
                }
            }
        }
    }

    public function restore(int $id): Asistencia
    {
        $model = Asistencia::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Asistencia eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
