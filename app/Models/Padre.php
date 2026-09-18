<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Padre extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'padres';

    protected $fillable = [
        'dni',
        'nombres',
        'apellidos',
        'telefono',
        'email',
        'direccion',
        'ocupacion',
        'estado_id',
        'apoderado_id',
    ];

    protected $with = ['estado', 'apoderado'];

    protected function casts(): array
    {
        return [
            'estado_id' => 'integer',
            'apoderado_id' => 'integer',
        ];
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estado::class, 'estado_id', 'id');
    }

    public function apoderado(): BelongsTo
    {
        return $this->belongsTo(Apoderado::class, 'apoderado_id', 'id');
    }
}
