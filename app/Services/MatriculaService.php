<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Padre;
use App\Models\Periodo;
use App\Models\Seccion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatriculaService
{
    public function getAll(): Collection
    {
        return Matricula::with(['estudiante', 'seccion', 'periodo', 'tipoMatricula', 'estado'])->get();
    }

    public function getById(int $id): Matricula
    {
        $model = Matricula::with(['estudiante', 'seccion', 'periodo', 'tipoMatricula', 'estado'])->find($id);

        if (! $model) {
            throw new NotFoundException('Matrícula', $id);
        }

        return $model;
    }

    public function create(array $data): Matricula
    {
        $this->assertPuedeGestionarMatricula();
        $this->validarLogicaEscuela($data);
        return Matricula::create($data);
    }

    public function update(int $id, array $data): Matricula
    {
        $this->assertPuedeGestionarMatricula();
        $model = $this->getById($id);
        $fullData = array_merge($model->toArray(), array_filter($data, fn ($v) => ! is_null($v)));
        $this->validarLogicaEscuela($fullData, $id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $this->assertPuedeGestionarMatricula();
        $model = $this->getById($id);

        $bloqueos = ['calificaciones', 'asistencias'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Matrícula; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    private function assertPuedeGestionarMatricula(): void
    {
        $user = auth()->user();
        if (! $user) return;
        if ((int) $user->rol_id === 1) return;
        throw new EnUsoException('Solo el administrador puede gestionar matrículas.');
    }

    private function validarLogicaEscuela(array $data, ?int $ignoreId = null): void
    {
        $estudianteId = $data['estudiante_id'] ?? null;
        $seccionId = $data['seccion_id'] ?? null;
        $periodoId = $data['periodo_id'] ?? null;
        $fecha = $data['fecha'] ?? null;

        if ($estudianteId && $seccionId) {
            $est = Estudiante::find($estudianteId);
            $sec = Seccion::with('grado')->find($seccionId);
            if ($est && $sec && $est->nivel_id && $sec->grado) {
                if ((int) $est->nivel_id !== (int) $sec->grado->nivel_id) {
                    throw ValidationException::withMessages(['seccion_id' => 'El nivel del estudiante no coincide con el nivel de la sección.']);
                }
            }
            if ($sec && $sec->vacantes > 0) {
                $q = Matricula::where('seccion_id', $seccionId)->whereNull('deleted_at');
                if ($ignoreId) $q->where('id', '!=', $ignoreId);
                if ($q->count() >= $sec->vacantes) {
                    throw ValidationException::withMessages(['seccion_id' => 'La sección ya alcanzó su límite de vacantes ('.$sec->vacantes.').']);
                }
            }
        }

        if ($periodoId && $fecha) {
            $per = Periodo::find($periodoId);
            if ($per && $per->fecha_inicio && $per->fecha_fin) {
                if ($fecha < $per->fecha_inicio || $fecha > $per->fecha_fin) {
                    throw ValidationException::withMessages(['fecha' => 'La fecha de matrícula debe estar dentro del periodo ('.$per->fecha_inicio.' a '.$per->fecha_fin.').']);
                }
            }
        }
    }

    public function restore(int $id): Matricula
    {
        $model = Matricula::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Matricula eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }

    /**
     * Registro unificado de matrícula (formulario único de secretaría):
     * resuelve o crea padre + estudiante y crea la matrícula, todo en transacción.
     *
     * @param array{estudiante: array, padre?: ?array, matricula: array} $payload
     */
    public function matricular(array $payload): Matricula
    {
        $this->assertPuedeGestionarMatricula();

        return DB::transaction(function () use ($payload) {
            $padreService = app(PadreService::class);
            $estudianteService = app(EstudianteService::class);

            // 1. Resolver padre (opcional)
            $padre = null;
            $padreInput = $payload['padre'] ?? null;
            if (is_array($padreInput) && ($padreInput['modo'] ?? null)) {
                if ($padreInput['modo'] === 'existente') {
                    $padre = Padre::find($padreInput['id'] ?? null);
                    if (! $padre) {
                        throw ValidationException::withMessages(['padre.id' => 'Seleccione un padre válido.']);
                    }
                } elseif ($padreInput['modo'] === 'nuevo') {
                    $padreData = $padreInput;
                    unset($padreData['modo'], $padreData['id']);
                    $padre = $padreService->create(array_filter($padreData, fn ($v) => ! is_null($v) && $v !== ''));
                }
            }

            // 2. Resolver estudiante
            $estInput = $payload['estudiante'] ?? [];
            if (($estInput['modo'] ?? null) === 'existente') {
                $estudiante = Estudiante::find($estInput['id'] ?? null);
                if (! $estudiante) {
                    throw ValidationException::withMessages(['estudiante.id' => 'Seleccione un estudiante válido.']);
                }
                // Vincular al hub del padre si el estudiante aún no tiene apoderado
                if (! $estudiante->apoderado_id && $padre?->apoderado_id) {
                    $estudiante->apoderado_id = $padre->apoderado_id;
                    $estudiante->save();
                }
            } else {
                $estData = $estInput;
                unset($estData['modo'], $estData['id']);
                if ($padre) {
                    $estData['padre_id'] = $padre->id;
                }
                $estudiante = $estudianteService->create(array_filter($estData, fn ($v) => ! is_null($v) && $v !== ''));
            }

            // 3. Evitar matrícula duplicada en el mismo periodo
            $matData = $payload['matricula'] ?? [];
            if (Matricula::where('estudiante_id', $estudiante->id)
                ->where('periodo_id', $matData['periodo_id'] ?? null)
                ->exists()) {
                throw ValidationException::withMessages(['matricula.periodo_id' => 'El estudiante ya tiene matrícula en este periodo.']);
            }

            $matData['estudiante_id'] = $estudiante->id;

            return $this->create($matData);
        });
    }
}
