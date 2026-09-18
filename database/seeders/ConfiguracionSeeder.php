<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('configuraciones')->insertOrIgnore([
            ['clave' => 'nombre_institucion', 'valor' => 'I.E. EGA', 'descripcion' => 'Nombre oficial de la institución educativa'],
            ['clave' => 'codigo_modular', 'valor' => '', 'descripcion' => 'Código modular de la IE ante MINEDU'],
            ['clave' => 'director', 'valor' => 'Zumaran Rios Victor Alberto', 'descripcion' => 'Director y sponsor del proyecto'],
            ['clave' => 'direccion', 'valor' => '', 'descripcion' => 'Dirección de la institución'],
            ['clave' => 'telefono', 'valor' => '', 'descripcion' => 'Teléfono de la institución'],
            ['clave' => 'correo', 'valor' => '', 'descripcion' => 'Correo institucional'],
            ['clave' => 'nota_minima_aprobatoria', 'valor' => '11', 'descripcion' => 'Nota mínima aprobatoria vigente'],
        ]);
    }
}