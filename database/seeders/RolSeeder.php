<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['nombre' => 'ADMIN', 'descripcion' => 'Acceso total al sistema (director/administrativo)'],
            ['nombre' => 'DOCENTE', 'descripcion' => 'Ingresa notas, asistencia y observaciones'],
        ]);
    }
}