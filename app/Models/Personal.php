<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personal extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'personal';

    protected $fillable = [
        'dni',
        'nombres',
        'apellidos',
        'categoria',
        'cargo',
        'telefono',
        'email',
        'fecha_ingreso',
        'estado_id',
        'user_id',
        'foto',
        'observaciones',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function asistenciasPersonal(): HasMany
    {
        return $this->hasMany(AsistenciaPersonal::class, 'personal_id', 'id');
    }
}
