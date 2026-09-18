<?php

namespace Database\Seeders;

use App\Models\Docente;
use Illuminate\Database\Seeder;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        $docentes = [
            ['dni' => '10000001', 'nombres' => 'Docente 1', 'apellidos' => 'Placeholder', 'especialidad' => 'General', 'email' => 'docente1@ega.edu.pe'],
            ['dni' => '10000002', 'nombres' => 'Docente 2', 'apellidos' => 'Placeholder', 'especialidad' => 'General', 'email' => 'docente2@ega.edu.pe'],
            ['dni' => '10000003', 'nombres' => 'Docente 3', 'apellidos' => 'Placeholder', 'especialidad' => 'General', 'email' => 'docente3@ega.edu.pe'],
        ];

        foreach ($docentes as $docente) {
            Docente::firstOrCreate(['dni' => $docente['dni']], $docente);
        }
    }
}