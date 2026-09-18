<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'areas';

    protected $fillable = [
        'area_padre_id', 
        'nombre', 
        'codigo_siagie'
    ];

    protected $with = ['areaPadre'];

    protected function casts(): array
    {
        return [
            'area_padre_id' => 'integer',
        ];
    }


    public function areaPadre(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Area::class, 'area_padre_id', 'id');
    }

    public function areasHijas(): HasMany
    {
        return $this->hasMany(\App\Models\Area::class, 'area_padre_id', 'id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(\App\Models\Calificacion::class, 'area_id', 'id');
    }
}
