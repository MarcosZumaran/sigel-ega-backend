<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = DB::table('roles')->where('nombre', 'ADMIN')->value('id');
        $estadoActivo = DB::table('estados')->where('nombre', 'Activo')->where('tipo_aplica', 'usuario')->value('id');

        DB::table('users')->insertOrIgnore([
            'name' => 'Administrador SIGEL-EGA',
            'email' => 'admin@ega.edu.pe',
            'password' => Hash::make('admin123'),
            'rol_id' => $rolAdmin,
            'estado_id' => $estadoActivo,
        ]);
    }
}