<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoMatricula extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'tipos_matricula';

    protected $fillable = [
        'nombre', 
        'descripcion'
    ];


    public function matriculas(): HasMany
    {
        return $this->hasMany(\App\Models\Matricula::class, 'tipo_matricula_id', 'id');
    }
}
