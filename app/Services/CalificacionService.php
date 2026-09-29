<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Bimestre;
use App\Models\Calificacion;
use App\Models\Docente;
use App\Models\Matricula;
use App\Models\Personal;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CalificacionService
{
    public function getAll(array $filters = []): Collection
    {
        $query = Calificacion::with(['matricula', 'area', 'tipoEvaluacion']);

        if (! empty($filters['area_id'])) {
            $query->where('area_id', (int) $filters['area_id']);
        }

        if (! empty($filters['tipo_evaluacion_id'])) {
            $query->where('tipo_evaluacion_id', (int) $filters['tipo_evaluacion_id']);
        }

        if (! empty($filters['bimestre_id'])) {
            $query->where('bimestre_id', (int) $filters['bimestre_id']);
        }

        $matriculaFilters = array_filter([
            'periodo_id' => $filters['periodo_id'] ?? null,
            'seccion_id' => $filters['seccion_id'] ?? null,
        ]);

        $nivelId = $filters['nivel_id'] ?? null;
        $gradoId = $filters['grado_id'] ?? null;

        if ($matriculaFilters || $nivelId || $gradoId) {
            $query->whereHas('matricula', function ($q) use ($matriculaFilters, $nivelId, $gradoId) {
                foreach ($matriculaFilters as $col => $val) {
                    $q->where($col, (int) $val);
                }
                if ($nivelId || $gradoId) {
                    $q->whereHas('seccion', function ($sq) use ($nivelId, $gradoId) {
                        if ($gradoId) {
                            $sq->where('grado_id', (int) $gradoId);
                        }
                        if ($nivelId) {
                            $sq->whereHas('grado', fn ($gq) => $gq->where('nivel_id', (int) $nivelId));
                        }
                    });
                }
            });
        }

        return $query->get();
    }

    public function getById(int $id): Calificacion
    {
        $model = Calificacion::with(['matricula', 'area', 'tipoEvaluacion'])->find($id);

        if (! $model) {
            throw new NotFoundException('Calificación', $id);
        }

        return $model;
    }

    public function create(array $data): Calificacion
    {
        $this->validar($data);
        $this->assertBimestreAbierto(isset($data['bimestre_id']) ? (int) $data['bimestre_id'] : null);
        $this->assertPuedeGestionarCalificacion($data);
        return Calificacion::create($data);
    }

    public function update(int $id, array $data): Calificacion
    {
        $model = $this->getById($id);
        $fullData = array_merge($model->toArray(), array_filter($data, fn ($v) => ! is_null($v)));
        $this->validar($fullData);
        $this->assertBimestreAbierto(isset($fullData['bimestre_id']) ? (int) $fullData['bimestre_id'] : ($model->bimestre_id ? (int) $model->bimestre_id : null));
        $this->assertPuedeGestionarCalificacion($fullData, $model);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);
        $this->assertBimestreAbierto($model->bimestre_id ? (int) $model->bimestre_id : null);
        $this->assertPuedeGestionarCalificacion($model->toArray(), $model);
        $model->delete();
    }

    private function assertBimestreAbierto(?int $bimestreId): void
    {
        if (! $bimestreId) return;
        $bim = Bimestre::find($bimestreId);
        if ($bim && ! (bool) $bim->activo) {
            throw new HttpException(403, 'El bimestre está cerrado. No se pueden modificar calificaciones.');
        }
    }

    private function assertPuedeGestionarCalificacion(array $data, ?Calificacion $existing = null): void
    {
        $user = auth()->user();
        if (! $user) return;
        if ((int) $user->rol_id === 1) return;
        $matId = $data['matricula_id'] ?? $existing?->matricula_id;
        if (! $matId) throw new EnUsoException('Solo el administrador puede gestionar calificaciones sin matrícula.');
        $mat = Matricula::with('seccion')->find($matId);
        if (! $mat || ! $mat->seccion) throw new EnUsoException('Matrícula o sección no encontrada.');
        $docenteId = $mat->seccion->docente_id;
        $personal = Personal::where('user_id', $user->id)->first();
        $docenteMatch = false;
        if ($personal && $personal->categoria === 'docente') {
            $docenteMatch = ((int) $personal->id === (int) $docenteId) || ((int) $personal->id <=3 && (int) $docenteId <=3 && (int) $personal->id === (int) $docenteId);
            if (! $docenteMatch) {
                $doc = Docente::find($docenteId);
                if ($doc && $personal->dni === $doc->dni) $docenteMatch = true;
            }
        } else {
            $doc = Docente::where('dni', $user->email)->first();
        }
        if (! $docenteMatch) {
            throw new EnUsoException('Solo el docente de la sección o el administrador puede gestionar calificaciones de esa matrícula.');
        }
    }

    private function validar(array $data): void
    {
        $esC = $data['es_nota_c'] ?? false;
        $motivo = $data['motivo_nota_c'] ?? null;
        $nivel = $data['nivel_logro'] ?? null;
        $nota = $data['nota'] ?? null;

        $esCBool = filter_var($esC, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $esC;

        if ($nivel === 'C') $esCBool = true;
        if ($esCBool && empty(trim((string) $motivo))) {
            throw ValidationException::withMessages(['motivo_nota_c' => 'La nota C / nivel C exige motivo (¿por qué C? competencia no lograda).']);
        }
        if ($nivel && ! in_array($nivel, ['AD','A','B','C'], true)) {
            throw ValidationException::withMessages(['nivel_logro' => 'Nivel debe ser AD, A, B o C (CNEB).']);
        }

        // Coherencia vigesimal → literal CNEB: 18-20 AD, 14-17 A, 11-13 B, 0-10 C
        if ($nota !== null && $nivel !== null && $nivel !== '') {
            $n = (float) $nota;
            $esperado = match (true) {
                $n >= 18 => 'AD',
                $n >= 14 => 'A',
                $n >= 11 => 'B',
                default => 'C',
            };
            if ($nivel !== $esperado) {
                throw ValidationException::withMessages([
                    'nivel_logro' => "Incoherencia CNEB: nota {$nota} corresponde a nivel {$esperado}, no {$nivel} (18-20 AD, 14-17 A, 11-13 B, 0-10 C).",
                ]);
            }
        }
    }

    public function restore(int $id): Calificacion
    {
        $model = Calificacion::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Calificacion eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }
}
