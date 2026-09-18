<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Apoderado extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'apoderados';

    /**
     * Nodo hub: agrupa padres y estudiantes. Sin datos personales.
     */
    protected $fillable = [
        'uuid',
    ];

    protected static function booted(): void
    {
        static::creating(function (Apoderado $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function padres(): HasMany
    {
        return $this->hasMany(Padre::class, 'apoderado_id', 'id');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'apoderado_id', 'id');
    }
}
