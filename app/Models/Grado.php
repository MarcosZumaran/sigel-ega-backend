<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'grados';

    protected $fillable = [
        'nivel_id', 
        'nombre'
    ];

    protected $with = ['nivel'];

    protected function casts(): array
    {
        return [
            'nivel_id' => 'integer',
        ];
    }


    public function nivel(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Nivel::class, 'nivel_id', 'id');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(\App\Models\Seccion::class, 'grado_id', 'id');
    }
}
