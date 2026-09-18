<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'asistencias';

    protected $fillable = [
        'matricula_id', 
        'fecha', 
        'estado'
    ];

    protected $with = ['matricula'];

    protected function casts(): array
    {
        return [
            'matricula_id' => 'integer',
        ];
    }


    public function matricula(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Matricula::class, 'matricula_id', 'id');
    }
}
