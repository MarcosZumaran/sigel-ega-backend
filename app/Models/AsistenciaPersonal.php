<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsistenciaPersonal extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'asistencias_personal';

    protected $fillable = [
        'personal_id',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'estado',
        'descripcion_justificacion',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id', 'id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(AsistenciaPersonalArchivo::class, 'asistencia_id', 'id');
    }
}
