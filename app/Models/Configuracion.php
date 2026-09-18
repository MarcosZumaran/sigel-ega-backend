<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'configuraciones';

    protected $fillable = [
        'clave', 
        'valor', 
        'descripcion'
    ];

}
