<?php

namespace App\Console\Commands;

use App\Models\Bimestre;
use App\Models\Periodo;
use App\Services\BimestreService;
use Illuminate\Console\Command;

class GenerarBimestres extends Command
{
    protected $signature = 'sigel:generar-bimestres {periodo_id} {anio?} {--force : Sobrescribir bimestres existentes}';

    protected $description = 'Genera los 4 bimestres de un periodo con fechas oficiales MINEDU (editables luego)';

    public function handle(): int
    {
        $periodo = Periodo::query()->findOrFail($this->argument('periodo_id'));
        $anio = (int) ($this->argument('anio') ?? $periodo->anio ?? now()->year);

        $existentes = Bimestre::query()->where('periodo_id', $periodo->id)->count();
        if ($existentes > 0 && ! $this->option('force')) {
            $this->warn("El periodo {$periodo->nombre} ya tiene {$existentes} bimestres. Use --force para actualizar fechas y nombres sin borrar calificaciones.");
            return self::FAILURE;
        }

        $rangos = BimestreService::rangosParaAnio($anio);
        $estimadas = ! BimestreService::esOficial($anio);
        if ($estimadas) {
            $this->warn("Año {$anio} sin fechas oficiales: se usan fechas estimadas (36 semanas lectivas en 4 bloques de 9). Ajústelas luego.");
        }

        foreach ($rangos as $numero => $rango) {
            $actual = Bimestre::query()->where('periodo_id', $periodo->id)->where('numero', $numero)->first();

            $bimestre = Bimestre::query()->updateOrCreate(
                ['periodo_id' => $periodo->id, 'numero' => $numero],
                [
                    'nombre' => "{$numero}° Bimestre",
                    'fecha_inicio' => "{$anio}-{$rango['inicio']}",
                    'fecha_fin' => "{$anio}-{$rango['fin']}",
                    'activo' => $actual?->activo ?? false,
                ]
            );

            $this->line("B{$numero}: {$bimestre->fecha_inicio} - {$bimestre->fecha_fin}");
        }

        $this->info("4 bimestres generados para el periodo {$periodo->nombre} (año {$anio}).");
        return self::SUCCESS;
    }
}
