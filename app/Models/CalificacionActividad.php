<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class CalificacionActividad extends Model
{
    use Auditable;

    protected $table = 'calificaciones_actividades';

    protected $fillable = [
        'actividad_id',
        'matricula_id',
        'nivel_logro',
    ];

    protected $casts = [
        'actividad_id' => 'integer',
        'matricula_id' => 'integer',
    ];

    protected $with = ['actividad', 'matricula'];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class);
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }
}
