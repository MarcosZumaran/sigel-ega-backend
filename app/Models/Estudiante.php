<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estudiante extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'estudiantes';

    protected $fillable = [
        'codigo_estudiante',
        'dni',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'pais_nacimiento',
        'departamento_nacimiento',
        'provincia_nacimiento',
        'distrito_nacimiento',
        'sexo',
        'lengua_materna',
        'autoidentificacion_etnica',
        'tiene_discapacidad',
        'tipo_discapacidad',
        'grado_discapacidad',
        'tiene_certificado_discapacidad',
        'direccion',
        'telefono',
        'email',
        'nivel_id',
        'grado_id',
        'estado_id',
        'apoderado_id',
    ];

    protected $with = ['nivel', 'grado', 'estado', 'apoderado'];

    protected function casts(): array
    {
        return [
            'nivel_id' => 'integer',
            'grado_id' => 'integer',
            'estado_id' => 'integer',
            'apoderado_id' => 'integer',
            'tiene_discapacidad' => 'boolean',
            'tiene_certificado_discapacidad' => 'boolean',
        ];
    }


    public function nivel(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Nivel::class, 'nivel_id', 'id');
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Grado::class, 'grado_id', 'id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estado::class, 'estado_id', 'id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(\App\Models\Matricula::class, 'estudiante_id', 'id');
    }

    public function apoderado(): BelongsTo
    {
        return $this->belongsTo(Apoderado::class, 'apoderado_id', 'id');
    }

    /**
     * Necesidad educativa especial del estudiante (0..1, única por estudiante_id).
     */
    public function necesidadEspecial(): HasOne
    {
        return $this->hasOne(\App\Models\NecesidadEspecial::class, 'estudiante_id', 'id');
    }

    /**
     * Nombre completo "apellidos, nombres" (usado por export SIAGIE).
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim(trim($this->apellidos ?? '').', '.trim($this->nombres ?? ''), ', ');
    }
}
