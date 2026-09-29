<?php

namespace App\Console\Commands;

use App\Models\Estudiante;
use App\Services\MatriculaService;
use Illuminate\Console\Command;

class SyncEstudiantesGrado extends Command
{
    protected $signature = 'sigel:sync-estudiantes-grado';

    protected $description = 'Sincroniza nivel_id, grado_id y estado_id de estudiantes desde su matricula del periodo activo';

    public function handle(MatriculaService $matriculas): int
    {
        $count = 0;
        $sinMatricula = 0;

        Estudiante::query()->select('id')->chunkById(200, function ($estudiantes) use ($matriculas, &$count, &$sinMatricula) {
            foreach ($estudiantes as $estudiante) {
                if ($matriculas->sincronizarEstudiante($estudiante->id)) {
                    $count++;
                } else {
                    $sinMatricula++;
                }
            }
        });

        $this->info("Estudiantes sincronizados: {$count}. Sin matricula en periodo activo: {$sinMatricula}.");

        return self::SUCCESS;
    }
}
