<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Actividad;
use App\Models\Area;
use App\Models\AsignacionDocente;
use App\Models\Bimestre;
use App\Models\Docente;
use App\Models\Personal;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ActividadService
{
    /**
     * Lista actividades con filtros.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Actividad::with(['competencia', 'bimestre', 'seccion', 'creador']);

        if (! empty($filters['competencia_id'])) {
            $query->where('competencia_id', (int) $filters['competencia_id']);
        }
        if (! empty($filters['bimestre_id'])) {
            $query->where('bimestre_id', (int) $filters['bimestre_id']);
        }
        if (! empty($filters['seccion_id'])) {
            $query->where('seccion_id', (int) $filters['seccion_id']);
        }

        return $query->orderByDesc('fecha')->orderByDesc('id')->get();
    }

    public function getById(int $id): Actividad
    {
        $model = Actividad::with(['competencia', 'bimestre', 'seccion', 'creador', 'calificaciones'])->find($id);
        if (! $model) {
            throw new NotFoundException('Actividad', $id);
        }
        return $model;
    }

    /**
     * Crea una actividad.
     */
    public function create(array $data): Actividad
    {
        $this->validar($data);
        $this->assertBimestreAbierto((int) $data['bimestre_id']);
        $this->assertDocenteAsignado($data);

        $data['creado_por'] = auth()->id();
        return Actividad::create($data);
    }

    /**
     * Actualiza una actividad.
     */
    public function update(int $id, array $data): Actividad
    {
        $model = $this->getById($id);
        $this->assertBimestreAbierto((int) $model->bimestre_id);
        $this->validar(array_merge($model->toArray(), array_filter($data, fn ($v) => ! is_null($v))));
        $model->update($data);
        return $model;
    }

    /**
     * Elimina (soft delete).
     */
    public function delete(int $id): void
    {
        $model = $this->getById($id);
        $this->assertBimestreAbierto((int) $model->bimestre_id);
        $model->delete();
    }

    public function restore(int $id): Actividad
    {
        $model = Actividad::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Actividad', $id);
        }
        $model->restore();
        return $model->refresh();
    }

    // ==================== VALIDACIONES ====================

    private function validar(array $data): void
    {
        if (empty($data['titulo']) || mb_strlen($data['titulo']) < 3) {
            throw ValidationException::withMessages(['titulo' => 'El título debe tener al menos 3 caracteres.']);
        }
        if (empty($data['competencia_id'])) {
            throw ValidationException::withMessages(['competencia_id' => 'La competencia es obligatoria.']);
        }
        if (empty($data['bimestre_id'])) {
            throw ValidationException::withMessages(['bimestre_id' => 'El bimestre es obligatorio.']);
        }
        if (empty($data['seccion_id'])) {
            throw ValidationException::withMessages(['seccion_id' => 'La sección es obligatoria.']);
        }
    }

    private function assertBimestreAbierto(int $bimestreId): void
    {
        $bim = Bimestre::find($bimestreId);
        if ($bim && ! (bool) $bim->activo) {
            throw new HttpException(403, 'El bimestre está cerrado. No se pueden modificar actividades.');
        }
    }

    /**
     * Verifica que el usuario autenticado pueda gestionar actividades de esa sección.
     * ADMIN siempre puede. Docente debe estar asignado a la sección (y área si es secundaria).
     */
    private function assertDocenteAsignado(array $data): void
    {
        $user = auth()->user();
        if (! $user) return;
        if ($user->hasRole('ADMIN')) return;

        $seccionId = (int) ($data['seccion_id'] ?? 0);
        $competenciaId = (int) ($data['competencia_id'] ?? 0);

        $personal = Personal::where('user_id', $user->id)->first();
        if (! $personal) {
            throw new EnUsoException('No tiene perfil de personal asignado.');
        }

        $docente = Docente::where('dni', $personal->dni)->first();
        if (! $docente) {
            throw new EnUsoException('No tiene perfil de docente.');
        }

        // Verificar asignación: encargado de la sección O asignado a la competencia
        $competencia = Area::find($competenciaId);
        $areaPadreId = $competencia?->area_padre_id;

        $asignado = AsignacionDocente::query()
            ->where('docente_id', $docente->id)
            ->where('seccion_id', $seccionId)
            ->where(function ($q) use ($competenciaId, $areaPadreId) {
                $q->whereNull('area_id')  // encargado de toda la sección
                  ->orWhere('area_id', $competenciaId)
                  ->orWhere('area_id', $areaPadreId);
            })
            ->exists();

        if (! $asignado) {
            throw new EnUsoException('No está asignado a esa sección/área.');
        }
    }
}
