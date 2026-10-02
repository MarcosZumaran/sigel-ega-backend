<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiagieLog extends Model
{
    use Auditable;

    protected $table = 'siagie_logs';

    protected $fillable = [
        'usuario_id',
        'operacion',
        'archivo',
        'formato',
        'periodo_id',
        'seccion_id',
        'filas_procesadas',
        'filas_exitosas',
        'filas_con_error',
        'errores',
        'resultado',
        'duracion_ms',
        'ip_address',
        'observaciones',
    ];

    protected $casts = [
        'usuario_id' => 'integer',
        'periodo_id' => 'integer',
        'seccion_id' => 'integer',
        'filas_procesadas' => 'integer',
        'filas_exitosas' => 'integer',
        'filas_con_error' => 'integer',
        'errores' => 'array',
        'duracion_ms' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }
}
