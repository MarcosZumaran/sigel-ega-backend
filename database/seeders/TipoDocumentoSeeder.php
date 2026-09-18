<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_documento')->insertOrIgnore([
            ['nombre' => 'Acta', 'descripcion' => 'Acta de reunión, actas de notas, etc.'],
            ['nombre' => 'Oficio', 'descripcion' => 'Comunicación oficial'],
            ['nombre' => 'Resolución', 'descripcion' => 'Resolución de dirección'],
            ['nombre' => 'Memorando', 'descripcion' => 'Comunicación interna'],
            ['nombre' => 'Constancia', 'descripcion' => 'Constancia de vacante, de estudios, etc.'],
            ['nombre' => 'Ficha de observación', 'descripcion' => 'Ficha de observación de estudiante o docente'],
            ['nombre' => 'Informe', 'descripcion' => 'Informe técnico o pedagógico'],
        ]);
    }
}