<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    public $timestamps = false;

    protected $table = 'auditorias';

    protected $fillable = [
        'usuario_id',
        'accion',
        'tabla',
        'registro_id',
        'payload',
        'ip_address',
        'user_agent',
        'origen',
    ];

    protected $casts = [
        'payload' => 'array',
        'usuario_id' => 'integer',
        'registro_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
}
