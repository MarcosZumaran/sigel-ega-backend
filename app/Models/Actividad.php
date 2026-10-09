<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actividad extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $table = 'actividades';

    protected $fillable = [
        'competencia_id',
        'bimestre_id',
        'seccion_id',
        'titulo',
        'descripcion',
        'fecha',
        'creado_por',
    ];

    protected $casts = [
        'competencia_id' => 'integer',
        'bimestre_id' => 'integer',
        'seccion_id' => 'integer',
        'creado_por' => 'integer',
        'fecha' => 'date',
    ];

    protected $with = ['competencia', 'bimestre', 'seccion'];

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'competencia_id');
    }

    public function bimestre(): BelongsTo
    {
        return $this->belongsTo(Bimestre::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(CalificacionActividad::class);
    }
}
