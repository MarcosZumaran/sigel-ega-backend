<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NecesidadEspecial extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'necesidades_especiales';

    protected $fillable = [
        'estudiante_id',
        'servicio',
        'tipo_nee',
        'descripcion',
        'certificado_salud',
        'registro_solicitud',
        'estado_seho',
        'codigo_seho',
        'nombre_seho',
        'fecha_solicitud',
        'fecha_reincorporacion',
        'reincorporado',
        'poi_ruta',
        'evaluacion_psicopedagogica_ruta',
        'ajustes_razonables',
        'docente_sanee',
        'observaciones',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_reincorporacion' => 'date',
        'reincorporado' => 'boolean',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'id');
    }
}
