<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaPersonalArchivo extends Model
{
    use HasFactory;

    protected $table = 'asistencia_personal_archivos';

    protected $fillable = [
        'asistencia_id',
        'ruta_archivo',
        'nombre_original',
        'mime',
        'tamano',
    ];

    public function asistencia(): BelongsTo
    {
        return $this->belongsTo(AsistenciaPersonal::class, 'asistencia_id', 'id');
    }
}
