<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seccion extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'secciones';

    protected $fillable = [
        'grado_id', 
        'nombre', 
        'turno', 
        'vacantes', 
        'docente_id'
    ];

    protected $with = ['grado', 'docente'];

    protected function casts(): array
    {
        return [
            'grado_id' => 'integer',
            'vacantes' => 'integer',
            'docente_id' => 'integer',
        ];
    }


    public function grado(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Grado::class, 'grado_id', 'id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Docente::class, 'docente_id', 'id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(\App\Models\Matricula::class, 'seccion_id', 'id');
    }
}
