<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Matriculado', 'tipo_aplica' => 'estudiante'],
            ['nombre' => 'Retirado', 'tipo_aplica' => 'estudiante'],
            ['nombre' => 'Trasladado', 'tipo_aplica' => 'estudiante'],
            ['nombre' => 'Egresado', 'tipo_aplica' => 'estudiante'],
            ['nombre' => 'Activo', 'tipo_aplica' => 'usuario'],
            ['nombre' => 'Inactivo', 'tipo_aplica' => 'usuario'],
            ['nombre' => 'Borrador', 'tipo_aplica' => 'documento'],
            ['nombre' => 'Emitido', 'tipo_aplica' => 'documento'],
            ['nombre' => 'Archivado', 'tipo_aplica' => 'documento'],
        ];

        DB::table('estados')->insertOrIgnore($estados);
    }
}