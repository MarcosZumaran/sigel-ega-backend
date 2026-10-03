<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class ConclusionDescriptiva extends Model
{
    use Auditable;

    protected $table = 'conclusiones_descriptivas';

    protected $fillable = ['matricula_id', 'competencia_id', 'bimestre_id', 'texto'];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'competencia_id');
    }

    public function bimestre(): BelongsTo
    {
        return $this->belongsTo(Bimestre::class);
    }
}
