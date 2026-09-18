<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelGradoSeeder extends Seeder
{
    public function run(): void
    {
        $nivelPrimaria = DB::table('niveles')->where('nombre', 'Primaria')->value('id');
        if (!$nivelPrimaria) {
            $nivelPrimaria = DB::table('niveles')->insertGetId(
                ['nombre' => 'Primaria', 'descripcion' => 'Educación primaria']
            );
        }

        $nivelSecundaria = DB::table('niveles')->where('nombre', 'Secundaria')->value('id');
        if (!$nivelSecundaria) {
            $nivelSecundaria = DB::table('niveles')->insertGetId(
                ['nombre' => 'Secundaria', 'descripcion' => 'Educación secundaria']
            );
        }

        $grados = [];
        for ($i = 1; $i <= 6; $i++) {
            $grados[] = ['nivel_id' => $nivelPrimaria, 'nombre' => "{$i}° de Primaria"];
        }
        for ($i = 1; $i <= 5; $i++) {
            $grados[] = ['nivel_id' => $nivelSecundaria, 'nombre' => "{$i}° de Secundaria"];
        }

        DB::table('grados')->insertOrIgnore($grados);
    }
}