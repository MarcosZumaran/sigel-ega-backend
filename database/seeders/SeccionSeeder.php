<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\Grado;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class SeccionSeeder extends Seeder
{
    public function run(): void
    {
        $docentes = Docente::orderBy('id')->get();

        foreach (Grado::orderBy('id')->get() as $i => $grado) {
            $docente = $docentes->get($i % max($docentes->count(), 1));

            Seccion::firstOrCreate(
                ['grado_id' => $grado->id, 'nombre' => 'A'],
                [
                    'turno' => 'manana',
                    'vacantes' => 30,
                    'docente_id' => $docente?->id,
                ],
            );
        }
    }
}