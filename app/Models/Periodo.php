<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periodo extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'periodos';

    protected $fillable = [
        'nombre', 
        'anio', 
        'fecha_inicio', 
        'fecha_fin', 
        'activo'
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'activo' => 'boolean',
        ];
    }


    public function matriculas(): HasMany
    {
        return $this->hasMany(\App\Models\Matricula::class, 'periodo_id', 'id');
    }
}
