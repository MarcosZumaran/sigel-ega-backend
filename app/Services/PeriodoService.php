<?php

namespace App\Services;

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Periodo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PeriodoService
{
    public function getAll(): Collection
    {
        return Periodo::withCount('matriculas')->orderByDesc('anio')->get();
    }

    public function getById(int $id): Periodo
    {
        $model = Periodo::orderBy('id')->find($id);

        if (! $model) {
            throw new NotFoundException('Periodo', $id);
        }

        return $model;
    }

    public function create(array $data): Periodo
    {
        return Periodo::create($data);
    }

    public function update(int $id, array $data): Periodo
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model;
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);

        $bloqueos = ['matriculas'];

        foreach ($bloqueos as $relacion) {
            if ($model->{$relacion}()->exists()) {
                throw new EnUsoException('No se puede eliminar el Periodo; tiene registros asociados.');
            }
        }

        $model->delete();
    }

    public function restore(int $id): Periodo
    {
        $model = Periodo::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) {
            throw new NotFoundException('Periodo eliminado', $id);
        }
        $model->restore();
        return $model->refresh();
    }

    public function activar(int $id): Periodo
    {
        $periodo = $this->getById($id);

        DB::transaction(function () use ($periodo) {
            Periodo::query()->update(['activo' => false]);
            $periodo->update(['activo' => true]);
        });

        return $periodo->fresh();
    }

    /**
     * Promocion de estudiantes al siguiente grado del mismo nivel.
     * Logica portada de PeriodoWebController del proyecto original:
     * avanza grado_id al siguiente grado del nivel; ultimo grado cuenta como egresado.
     */
    public function promocionar(int $id): array
    {
        $this->getById($id);

        $grados = Grado::orderBy('nivel_id')->orderBy('id')->get(['id', 'nivel_id']);
        $siguiente = [];
        foreach ($grados->groupBy('nivel_id') as $lista) {
            $ids = $lista->pluck('id')->values();
            foreach ($ids as $i => $gid) {
                $siguiente[$gid] = $ids[$i + 1] ?? null;
            }
        }

        $promovidos = 0;
        $egresados = 0;

        DB::transaction(function () use ($siguiente, &$promovidos, &$egresados) {
            Estudiante::whereNotNull('grado_id')->chunkById(200, function ($estudiantes) use ($siguiente, &$promovidos, &$egresados) {
                foreach ($estudiantes as $est) {
                    $nx = $siguiente[$est->grado_id] ?? null;
                    if ($nx) {
                        $est->update(['grado_id' => $nx]);
                        $promovidos++;
                    } else {
                        $egresados++;
                    }
                }
            });
        });

        return ['promovidos' => $promovidos, 'egresados' => $egresados];
    }
}
