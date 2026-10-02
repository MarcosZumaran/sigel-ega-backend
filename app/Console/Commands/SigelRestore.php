<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

#[Signature('sigel:restore {archivo : Ruta del archivo .zip a restaurar} {--force : No pedir confirmación}')]
#[Description('Restaura un backup de SIGEL-EGA (REEMPLAZA los datos actuales)')]
class SigelRestore extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $archivo = $this->argument('archivo');

        // 1. Verificar existencia y formato
        if (! file_exists($archivo)) {
            $this->error("Archivo no encontrado: {$archivo}");

            return 1;
        }

        if (! str_ends_with($archivo, '.zip')) {
            $this->error('El archivo debe ser .zip');

            return 1;
        }

        // 2. Verificar integridad mínima
        $size = filesize($archivo);
        if ($size < 1024) {
            $this->error('El archivo está vacío o corrupto.');

            return 1;
        }

        // 3. Confirmación (a menos que --force)
        if (! $this->option('force')) {
            $this->warn('ADVERTENCIA: Esto REEMPLAZARÁ la base de datos actual.');
            $this->warn("Archivo: {$archivo}");
            $this->warn('Tamaño: '.round($size / 1024 / 1024, 2).' MB');

            if (! $this->confirm('¿Estás seguro de continuar?', false)) {
                $this->info('Operación cancelada.');

                return 0;
            }
        }

        $this->info('Restaurando backup...');
        $this->info('Esta operación puede tardar varios minutos.');

        // 4. Copiar el archivo al directorio de backups de Spatie
        $backupName = config('backup.backup.name');
        $disk = Storage::disk('local');
        $destino = $backupName.'/'.basename($archivo);
        $disk->put($destino, file_get_contents($archivo));

        // 5. Ejecutar restauración de Spatie
        $exitCode = Artisan::call('backup:restore', [
            '--disk' => 'local',
            '--backup' => basename($archivo, '.zip'),
        ]);

        if ($exitCode !== 0) {
            $this->error('La restauración falló. Revisa los logs.');

            return 1;
        }

        $this->newLine();
        $this->info('Restauración completada.');
        $this->info('Reinicia la aplicación para asegurar que los datos se recarguen.');

        return 0;
    }
}
