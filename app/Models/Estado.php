<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'estados';

    protected $fillable = [
        'nombre', 
        'tipo_aplica', 
        'descripcion'
    ];


    public function usuarios(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'estado_id', 'id');
    }
}
