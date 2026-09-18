<?php
namespace App\Console\Commands;
use App\Services\ReporteService;
use Illuminate\Console\Command;
class CacheReportes extends Command
{
    protected $signature = 'reportes:cache';
    protected $description = 'Genera cache nocturno de reportes auxiliares/asistencia para todas las secciones del periodo activo';
    public function handle(ReporteService $service): int
    {
        $count = $service->cacheNocturno();
        $this->info("Cache nocturno generado: {$count} reportes");
        return self::SUCCESS;
    }
}
