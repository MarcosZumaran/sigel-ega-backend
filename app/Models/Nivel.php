<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nivel extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'niveles';

    protected $fillable = [
        'nombre', 
        'descripcion'
    ];


    public function grados(): HasMany
    {
        return $this->hasMany(\App\Models\Grado::class, 'nivel_id', 'id');
    }
}
