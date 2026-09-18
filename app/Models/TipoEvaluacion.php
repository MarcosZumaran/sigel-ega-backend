<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoEvaluacion extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'tipos_evaluacion';

    protected $fillable = [
        'nombre', 
        'descripcion'
    ];


    public function calificaciones(): HasMany
    {
        return $this->hasMany(\App\Models\Calificacion::class, 'tipo_evaluacion_id', 'id');
    }
}
