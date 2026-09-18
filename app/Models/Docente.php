<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
