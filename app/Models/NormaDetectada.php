<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NormaDetectada extends Model
{
    protected $table = 'normas_detectadas';

    protected $fillable = ['anio', 'url_pdf', 'fechas_json', 'estado'];

    protected $casts = ['fechas_json' => 'array'];
}
