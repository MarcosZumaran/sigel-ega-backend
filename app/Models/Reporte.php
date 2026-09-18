<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reporte extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'reportes';

    protected $fillable = [
        'tipo',
        'periodo_id',
        'seccion_id',
        'formato',
        'parametros',
        'estado',
        'ruta_archivo',
        'hash',
        'generado_por',
        'expira_en',
    ];

    protected $casts = [
        'parametros' => 'array',
        'periodo_id' => 'integer',
        'seccion_id' => 'integer',
        'generado_por' => 'integer',
        'expira_en' => 'datetime',
    ];

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id', 'id');
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_id', 'id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por', 'id');
    }
}
