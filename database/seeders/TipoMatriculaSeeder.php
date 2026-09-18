<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoMatriculaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_matricula')->insertOrIgnore([
            ['nombre' => 'Regular', 'descripcion' => 'Matrícula anual regular'],
            ['nombre' => 'Traslado Ingreso', 'descripcion' => 'Ingreso por traslado desde otra IE'],
            ['nombre' => 'Traslado Egreso', 'descripcion' => 'Egreso por traslado a otra IE'],
        ]);
    }
}