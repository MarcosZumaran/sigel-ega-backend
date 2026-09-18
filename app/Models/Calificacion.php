<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'calificaciones';

    protected $fillable = [
        'matricula_id', 
        'area_id', 
        'tipo_evaluacion_id', 
        'nota', 
        'nivel_logro',
        'escala',
        'es_nota_c', 
        'motivo_nota_c'
    ];

    protected $with = ['matricula', 'area', 'tipoEvaluacion'];

    protected function casts(): array
    {
        return [
            'matricula_id' => 'integer',
            'area_id' => 'integer',
            'tipo_evaluacion_id' => 'integer',
            'nota' => 'float',
            'es_nota_c' => 'boolean',
        ];
    }


    public function matricula(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Matricula::class, 'matricula_id', 'id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Area::class, 'area_id', 'id');
    }

    public function tipoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(\App\Models\TipoEvaluacion::class, 'tipo_evaluacion_id', 'id');
    }
}
