<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parentesco extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'parentescos';

    protected $fillable = [
        'nombre'
    ];


    public function padreEstudiantes(): HasMany
    {
        return $this->hasMany(\App\Models\PadreEstudiante::class, 'parentesco_id', 'id');
    }
}
