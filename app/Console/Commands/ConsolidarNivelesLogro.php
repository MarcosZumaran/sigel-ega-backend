<?php

namespace App\Console\Commands;

use App\Services\EvaluacionService;
use Illuminate\Console\Command;

class ConsolidarNivelesLogro extends Command
{
    protected $signature = 'sigel:consolidar-niveles {periodo_id : ID del periodo a consolidar}';

    protected $description = 'Consolida los niveles de logro por competencia y área para todas las matrículas del periodo';

    public function handle(EvaluacionService $evaluacion): int
    {
        $resultado = $evaluacion->consolidarPeriodo((int) $this->argument('periodo_id'));

        $this->info("Estudiantes consolidados: {$resultado['estudiantes']}, niveles guardados: {$resultado['niveles']}.");

        return self::SUCCESS;
    }
}
