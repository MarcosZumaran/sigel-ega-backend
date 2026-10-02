<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Correlativo extends Model
{
    use Auditable;

    protected $table = 'correlativos';

    protected $fillable = [
        'tipo_documento_id',
        'anio',
        'ultimo_numero',
        'formato',
    ];

    protected $casts = [
        'tipo_documento_id' => 'integer',
        'anio' => 'integer',
        'ultimo_numero' => 'integer',
    ];

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }
}
