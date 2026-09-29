<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Padre;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EstudianteService
{
    public function getAll(): Collection
    {
        return Estudiante::with(['nivel', 'grado', 'estado', 'apoderado'])->get();
    }

    public function getById(int $id): Estudiante
    {
        $model = Estudiante::with(['nivel', 'grado', 'estado', 'apoderado'])->find($id);

        if (! $model) {
            throw new NotFoundException('Estudiante', $id);
        }

        $periodoActivo = \App\Models\Periodo::where('activo', true)->first();
        if ($periodoActivo) {
            $model->setRelation(
                'matricula_activa',
                \App\Models\Matricula::with('seccion.grado')
                    ->where('estudiante_id', $id)
                    ->where('periodo_id', $periodoActivo->id)
                    ->latest('id')
                    ->first()
            );
        }

        return $model;
    }

    public function create(array $data): Estudiante
    {
        // Flujo de secretaría: el estudiante hereda el apoderado del padre seleccionado.
        // padre_id es solo un selector (no es columna de estudiantes) y se descarta antes de crear.
        if (isset($data['padre_id']) && $data['padre_id']) {
            $padre = Padre::find($data['padre_id']);
            if (! $padre) {
                throw new NotFoundException('Padre de Familia', (int) $data['padre_id']);
            }
            if (! $padre->apoderado_id) {
                // Datos legados: el padre aún no tiene hub; se le crea uno ahora.
                $padre->apoderado_id = Apoderado::create(['uuid' => (string) Str::uuid()])->id;
                $padre->save();
            }
            $data['apoderado_id'] = $padre->apoderado_id;
        }
        unset($data['padre_id']);

        if (empty($data['apoderado_id'])) {
            // Caso excepcional: sin padre, se exige apoderado directo o crear al padre primero.
            throw ValidationException::withMessages([
                'padre_id' => 'Seleccione el padre para heredar su apoderado, o indique un apoderado existente.',
            ]);
        }

        $this->validar($data);
        $estudiante = Estudiante::create($data);

        return $estudiante->load(['apoderado', 'nivel', 'grado', 'estado']);
    }

    public function update(int $id, array $data): Estudiante
    {
        $model = $this->getById($id);
        $fullData = array_merge($model->toArray(), array_filter($data, fn ($v) => ! is_null($v)));
        $this->validar($fullData);
        $model->update($data);

        return $model;
    }

    private function validar(array $data): void
    {
        $fn = $data['fecha_nacimiento'] ?? null;
        if ($fn) {
            if ($fn > date('Y-m-d')) {
                throw ValidationException::withMessages(['fecha_nacimiento' => 'La fecha de nacimiento no puede ser futura.']);
            }
            $edad = (int) ((time() - strtotime($fn)) / 31557600);
            if ($edad < 3 || $edad > 25) {
                throw ValidationException::withMessages(['fecha_nacimiento' => 'Edad fuera de rango escolar (3-25 años). Verifique fecha.']);
            }
        }
        $nivelId = $data['nivel_id'] ?? null;
        $gradoId = $data['grado_id'] ?? null;
        if ($nivelId && $gradoId) {
            $grado = Grado::find($gradoId);
            if ($grado && (int) $grado->nivel_id !== (int) $nivelId) {
                throw ValidationException::withMessages(['grado_id' => 'El grado no pertenece al nivel indicado.']);
            }
        }
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['matriculas'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Estudiante; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Estudiante
    {
        $model = Estudiante::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Estudiante eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
