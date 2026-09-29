<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Asistencia;
use App\Models\Matricula;
use App\Models\Personal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsistenciaService
{
    public function getAll(array $filters = []): Collection
    {
        $q = Asistencia::with(['matricula.estudiante']);

        if (! empty($filters['matricula_id'])) {
            $q->where('matricula_id', $filters['matricula_id']);
        }
        if (! empty($filters['estado'])) {
            $q->where('estado', $filters['estado']);
        }
        if (! empty($filters['desde'])) {
            $q->where('fecha', '>=', $filters['desde']);
        }
        if (! empty($filters['hasta'])) {
            $q->where('fecha', '<=', $filters['hasta']);
        }
        if (! empty($filters['seccion_id']) || ! empty($filters['periodo_id'])) {
            $q->whereHas('matricula', function ($mq) use ($filters) {
                if (! empty($filters['seccion_id'])) {
                    $mq->where('seccion_id', $filters['seccion_id']);
                }
                if (! empty($filters['periodo_id'])) {
                    $mq->where('periodo_id', $filters['periodo_id']);
                }
            });
        }

        return $q->orderBy('fecha')->orderBy('matricula_id')->get();
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
        if (($data['estado'] ?? null) === 'justificado' && empty(trim((string) ($data['motivo_justificacion'] ?? '')))) {
            throw ValidationException::withMessages(['motivo_justificacion' => 'Justificado exige indicar un motivo.']);
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

    public function batchUpsert(array $items): array
    {
        return DB::transaction(function () use ($items) {
            $creados = 0;
            $actualizados = 0;
            $errores = [];

            foreach ($items as $i => $item) {
                try {
                    if (($item['estado'] ?? null) !== 'justificado') {
                        $item['motivo_justificacion'] = null;
                    }
                    $existing = Asistencia::where('matricula_id', $item['matricula_id'])
                        ->where('fecha', $item['fecha'])
                        ->first();
                    if ($existing) {
                        $this->update($existing->id, $item);
                        $actualizados++;
                    } else {
                        $this->create($item);
                        $creados++;
                    }
                } catch (ValidationException $e) {
                    foreach ($e->errors() as $field => $messages) {
                        foreach ((array) $messages as $msg) {
                            $errores[] = "Ítem {$i} ({$field}): {$msg}";
                        }
                    }
                }
            }

            if (! empty($errores)) {
                throw ValidationException::withMessages(['items' => $errores]);
            }

            return ['creados' => $creados, 'actualizados' => $actualizados, 'errores' => []];
        });
    }
}
