<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Docente extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'docentes';

    protected $fillable = [
        'dni', 
        'nombres', 
        'apellidos', 
        'especialidad', 
        'telefono', 
        'email'
    ];


    public function secciones(): HasMany
    {
        return $this->hasMany(\App\Models\Seccion::class, 'docente_id', 'id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionDocente::class);
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'asignaciones_docentes')
            ->withPivot(['seccion_id', 'es_encargado'])
            ->withTimestamps();
    }

    public function seccionesAsignadas(): BelongsToMany
    {
        return $this->belongsToMany(Seccion::class, 'asignaciones_docentes')
            ->withPivot(['area_id', 'es_encargado'])
            ->withTimestamps();
    }
}
