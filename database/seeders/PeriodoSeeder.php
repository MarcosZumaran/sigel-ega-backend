<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('periodos')->insertOrIgnore([
            'nombre' => '2026',
            'anio' => 2026,
            'fecha_inicio' => '2026-03-16',
            'fecha_fin' => '2026-12-18',
            'activo' => true,
        ]);
    }
}