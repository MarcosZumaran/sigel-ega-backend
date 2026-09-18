<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'roles';

    protected $fillable = [
        'nombre', 
        'descripcion'
    ];


    public function usuarios(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'rol_id', 'id');
    }
}
