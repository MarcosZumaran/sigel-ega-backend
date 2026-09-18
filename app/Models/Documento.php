<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'documentos';

    protected $fillable = [
        'tipo_documento_id', 
        'numero', 
        'asunto', 
        'destinatario', 
        'fecha', 
        'ruta_archivo', 
        'usuario_id'
    ];

    protected $with = ['tipoDocumento', 'usuario'];

    protected function casts(): array
    {
        return [
            'tipo_documento_id' => 'integer',
            'usuario_id' => 'integer',
        ];
    }


    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(\App\Models\TipoDocumento::class, 'tipo_documento_id', 'id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id', 'id');
    }
}
