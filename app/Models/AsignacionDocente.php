<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignacionDocente extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'asignaciones_docentes';

    protected $fillable = [
        'docente_id',
        'seccion_id',
        'area_id',
        'es_encargado',
    ];

    protected $casts = [
        'docente_id' => 'integer',
        'seccion_id' => 'integer',
        'area_id' => 'integer',
        'es_encargado' => 'boolean',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
