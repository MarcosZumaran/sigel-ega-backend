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
            // Claves FUM (solo se insertan si no existen; jamás sobrescriben valores configurados)
            ['clave' => 'resolucion_creacion', 'valor' => '', 'descripcion' => 'Resolución de creación de la IE'],
            ['clave' => 'ugel', 'valor' => '', 'descripcion' => 'UGEL a la que pertenece la IE'],
            ['clave' => 'dre', 'valor' => '', 'descripcion' => 'DRE a la que pertenece la IE'],
        ]);
    }
}