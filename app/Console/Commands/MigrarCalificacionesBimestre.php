<?php

namespace App\Console\Commands;

use App\Models\Bimestre;
use App\Models\Calificacion;
use Illuminate\Console\Command;

class MigrarCalificacionesBimestre extends Command
{
    protected $signature = 'sigel:migrar-calificaciones-bimestre {--periodo_id= : Solo este periodo}';
    protected $description = 'Asigna bimestre_id a calificaciones existentes según su fecha o el bimestre activo';

    public function handle(): int
    {
        $q = Calificacion::whereNull('bimestre_id')->with('matricula');
        if ($this->option('periodo_id')) {
            $pid = (int) $this->option('periodo_id');
            $q->whereHas('matricula', fn ($m) => $m->where('periodo_id', $pid));
        }
        $migradas = 0;
        $sinBimestre = 0;
        $q->chunkById(200, function ($califs) use (&$migradas, &$sinBimestre) {
            foreach ($califs as $c) {
                $pid = $c->matricula?->periodo_id;
                if (! $pid) {
                    $sinBimestre++;
                    continue;
                }
                $b = Bimestre::where('periodo_id', $pid)
                    ->where('fecha_inicio', '<=', $c->created_at->toDateString())
                    ->where('fecha_fin', '>=', $c->created_at->toDateString())
                    ->first()
                    ?? Bimestre::where('periodo_id', $pid)->where('activo', true)->first()
                    ?? Bimestre::where('periodo_id', $pid)->orderBy('numero')->first();
                if (! $b) {
                    $sinBimestre++;
                    continue;
                }
                $c->update(['bimestre_id' => $b->id]);
                $migradas++;
            }
        });
        $this->info("Migradas: $migradas | Sin bimestre disponible: $sinBimestre");
        return 0;
    }
}
