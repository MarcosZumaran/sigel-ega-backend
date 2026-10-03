<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matricula extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'matriculas';

    /**
     * Tipo de vacante según RM N° 193-2020-MINEDU: Regular, Ampliada, Virtual.
     */
    protected $fillable = [
        'estudiante_id', 
        'seccion_id', 
        'periodo_id', 
        'tipo_matricula_id',
        'tipo_vacante',
        'fecha', 
        'estado_id', 
        'observaciones'
    ];

    protected $with = ['estudiante', 'seccion', 'periodo', 'tipoMatricula', 'estado'];

    protected function casts(): array
    {
        return [
            'estudiante_id' => 'integer',
            'seccion_id' => 'integer',
            'periodo_id' => 'integer',
            'tipo_matricula_id' => 'integer',
            'estado_id' => 'integer',
        ];
    }


    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estudiante::class, 'estudiante_id', 'id');
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Seccion::class, 'seccion_id', 'id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Periodo::class, 'periodo_id', 'id');
    }

    public function tipoMatricula(): BelongsTo
    {
        return $this->belongsTo(\App\Models\TipoMatricula::class, 'tipo_matricula_id', 'id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estado::class, 'estado_id', 'id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(\App\Models\Calificacion::class, 'matricula_id', 'id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(\App\Models\Asistencia::class, 'matricula_id', 'id');
    }
}
