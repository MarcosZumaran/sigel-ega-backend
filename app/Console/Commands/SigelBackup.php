<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

#[Signature('sigel:backup {--destino= : Ruta absoluta donde copiar el backup (ej. /media/usb)} {--solo-bd : Solo respaldar la base de datos}')]
#[Description('Crea un backup completo del sistema SIGEL-EGA (BD + config)')]
class SigelBackup extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando respaldo de SIGEL-EGA...');

        // 1. Ejecutar backup de Spatie
        $exitCode = Artisan::call(
            'backup:run',
            $this->option('solo-bd') ? ['--only-db' => true] : []
        );

        if ($exitCode !== 0) {
            $this->error('El respaldo falló. Revisa los logs.');

            return 1;
        }

        // 2. Obtener el archivo más reciente
        $disk = Storage::disk('local');
        $backupName = config('backup.backup.name');
        $files = collect($disk->allFiles($backupName))
            ->filter(fn ($f) => str_ends_with($f, '.zip'))
            ->sortByDesc(fn ($f) => $disk->lastModified($f));

        if ($files->isEmpty()) {
            $this->error('No se encontró archivo de respaldo.');

            return 1;
        }

        $latest = $files->first();
        $latestPath = $disk->path($latest);
        $size = round($disk->size($latest) / 1024 / 1024, 2);

        $this->info("Respaldo creado: {$latest}");
        $this->info("Tamaño: {$size} MB");
        $this->info("Ubicación: {$latestPath}");

        // 3. Copiar a destino externo si se especificó
        if ($destino = $this->option('destino')) {
            if (! is_dir($destino)) {
                $this->error("El destino no existe: {$destino}");

                return 1;
            }
            $destinoFinal = rtrim($destino, '/').'/'.basename($latestPath);
            if (copy($latestPath, $destinoFinal)) {
                $this->info("Copiado a: {$destinoFinal}");
            } else {
                $this->error("No se pudo copiar a {$destinoFinal}");

                return 1;
            }
        }

        $this->newLine();
        $this->info('Respaldo completado exitosamente.');

        return 0;
    }
}
