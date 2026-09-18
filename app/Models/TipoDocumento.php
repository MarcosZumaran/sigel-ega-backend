<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'tipos_documento';

    protected $fillable = [
        'nombre', 
        'descripcion'
    ];


    public function documentos(): HasMany
    {
        return $this->hasMany(\App\Models\Documento::class, 'tipo_documento_id', 'id');
    }
}
