<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\NecesidadEspecial;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class NecesidadEspecialService
{
    public function getAll(): Collection
    {
        return NecesidadEspecial::with(['estudiante.nivel','estudiante.grado','estudiante.apoderado'])->orderBy('id')->get();
    }

    public function getById(int $id): NecesidadEspecial
    {
        $model = NecesidadEspecial::with(['estudiante.nivel','estudiante.grado','estudiante.apoderado'])->find($id);
        if (! $model) throw new NotFoundException('Necesidad especial', $id);
        return $model;
    }

    public function create(array $data): NecesidadEspecial
    {
        $this->validar($data);
        if (isset($data['estudiante_id']) && NecesidadEspecial::where('estudiante_id', $data['estudiante_id'])->exists()) {
            throw ValidationException::withMessages(['estudiante_id' => 'El estudiante ya tiene un registro de necesidad especial.']);
        }
        return NecesidadEspecial::create($data);
    }

    public function update(int $id, array $data): NecesidadEspecial
    {
        $model = $this->getById($id);
        $fullData = array_merge($model->toArray(), array_filter($data, fn($v)=>!is_null($v)));
        $this->validar($fullData, $id);
        if (isset($data['estudiante_id']) && $data['estudiante_id'] != $model->estudiante_id) {
            if (NecesidadEspecial::where('estudiante_id', $data['estudiante_id'])->where('id','!=',$id)->exists()) {
                throw ValidationException::withMessages(['estudiante_id' => 'El estudiante ya tiene un registro de necesidad especial.']);
            }
        }
        $model->update($data);
        return $model->refresh();
    }

    public function delete(int $id): void
    {
        $model = $this->getById($id);
        $model->delete();
    }

    public function restore(int $id): NecesidadEspecial
    {
        $model = NecesidadEspecial::withTrashed()->find($id);
        if (! $model || ! $model->trashed()) throw new NotFoundException('Necesidad especial eliminada', $id);
        $model->restore();
        return $model->refresh();
    }

    private function validar(array $data, ?int $ignoreId=null): void
    {
        $servicio = $data['servicio'] ?? null;
        $estado = $data['estado_seho'] ?? null;
        $codigo = $data['codigo_seho'] ?? null;
        $nombre = $data['nombre_seho'] ?? null;
        $fechaSol = $data['fecha_solicitud'] ?? null;
        $fechaReinc = $data['fecha_reincorporacion'] ?? null;
        $reinc = $data['reincorporado'] ?? false;

        if ($servicio === 'SEHO') {
            if (!$estado) throw ValidationException::withMessages(['estado_seho' => 'SEHO exige estado_seho (pendiente/en_atencion/atendido/reincorporado).']);
            if (!$codigo) throw ValidationException::withMessages(['codigo_seho' => 'SEHO exige codigo_seho.']);
            if (!$nombre) throw ValidationException::withMessages(['nombre_seho' => 'SEHO exige nombre_seho (hospital/CREBE).']);
        }
        if ($fechaSol && $fechaReinc && $fechaReinc < $fechaSol) {
            throw ValidationException::withMessages(['fecha_reincorporacion' => 'La reincorporación no puede ser anterior a la solicitud.']);
        }
        if ($reinc && !$fechaReinc) {
            throw ValidationException::withMessages(['fecha_reincorporacion' => 'Reincorporado exige fecha_reincorporacion.']);
        }
        if ($fechaSol && $fechaSol > date('Y-m-d')) {
            throw ValidationException::withMessages(['fecha_solicitud' => 'Fecha de solicitud no puede ser futura.']);
        }
    }
}
