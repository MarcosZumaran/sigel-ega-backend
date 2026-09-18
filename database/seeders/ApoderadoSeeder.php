<?php

namespace Database\Seeders;

use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Padre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApoderadoSeeder extends Seeder
{
    /**
     * Crea nodos hub (sin datos personales) y vincula padres/estudiantes
     * existentes cuando los haya. Los datos personales viven en
     * padres y estudiantes, nunca en apoderados.
     */
    public function run(): void
    {
        $hub1 = Apoderado::firstOrCreate(['uuid' => (string) Str::uuid()]);
        $hub2 = Apoderado::firstOrCreate(['uuid' => (string) Str::uuid()]);

        // Vinculación defensiva: solo si hay registros sin apoderado asignado.
        $padresLibres = Padre::whereNull('apoderado_id')->orderBy('id')->take(3)->pluck('id');
        foreach ($padresLibres as $i => $padreId) {
            Padre::whereKey($padreId)->update([
                'apoderado_id' => $i < 2 ? $hub1->id : $hub2->id,
            ]);
        }

        $estudiantesLibres = Estudiante::whereNull('apoderado_id')->orderBy('id')->take(3)->pluck('id');
        foreach ($estudiantesLibres as $i => $estudianteId) {
            Estudiante::whereKey($estudianteId)->update([
                'apoderado_id' => $i < 2 ? $hub1->id : $hub2->id,
            ]);
        }
    }
}
