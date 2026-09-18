<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoEvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_evaluacion')->insertOrIgnore([
            ['nombre' => 'Diagnóstica', 'descripcion' => 'Evaluación diagnóstica de inicio de año'],
            ['nombre' => 'Proceso', 'descripcion' => 'Evaluación de proceso'],
            ['nombre' => 'Bimestral', 'descripcion' => 'Evaluación de bimestre'],
            ['nombre' => 'Refuerzo Escolar', 'descripcion' => 'Informe de refuerzo (SIMON)'],
        ]);
    }
}