<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class NivelLogroConsolidado extends Model
{
    use Auditable;

    protected $table = 'niveles_logro_consolidados';

    protected $fillable = ['matricula_id', 'competencia_id', 'area_id', 'bimestre_id', 'nivel', 'es_final'];

    protected $casts = ['es_final' => 'boolean'];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'competencia_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function bimestre(): BelongsTo
    {
        return $this->belongsTo(Bimestre::class);
    }
}
