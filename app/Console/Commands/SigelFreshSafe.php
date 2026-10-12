<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SigelFreshSafe extends Command
{
    protected $signature = 'sigel:fresh-safe
                            {--seed : Ejecutar seeders después del fresh}
                            {--force : No pedir confirmación (solo para CI)}';

    protected $description = 'Drop + recreate de BD con backup automático previo (evita pérdida de datos)';

    public function handle(): int
    {
        // Bloquear en producción salvo --force explícito
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Comando bloqueado en producción. Usar --force solo si sabés lo que hacés.');

            return 1;
        }

        $this->warn('==============================================');
        $this->warn('  ATENCION: este comando BORRA toda la BD.');
        $this->warn('==============================================');
        $this->newLine();

        // 1. Backup automático
        $this->info('Paso 1/3: Backup automático previo...');
        $exitBackup = Artisan::call('sigel:backup');
        if ($exitBackup !== 0) {
            $this->error('Backup falló. Abortando por seguridad.');

            return 1;
        }

        // Confirmar que el backup se creó
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $backupName = config('backup.backup.name');
        $files = collect($disk->allFiles($backupName))
            ->filter(fn ($f) => str_ends_with($f, '.zip'))
            ->sortByDesc(fn ($f) => $disk->lastModified($f));

        if ($files->isEmpty()) {
            $this->error('No se encontró archivo de backup tras la ejecución. Abortando.');

            return 1;
        }

        $ultimoBackup = $files->first();
        $rutaBackup = $disk->path($ultimoBackup);
        $tamanoMb = round($disk->size($ultimoBackup) / 1024 / 1024, 2);

        $this->info("  Backup OK: {$ultimoBackup} ({$tamanoMb} MB)");
        $this->newLine();

        // 2. Confirmación explícita
        if (! $this->option('force')) {
            $this->warn('Paso 2/3: Confirmación');
            $this->warn('  Se va a ejecutar: php artisan migrate:fresh'.($this->option('seed') ? ' --seed' : ''));
            $this->warn("  Los datos actuales se perderán (backup en: {$rutaBackup}).");
            $this->newLine();

            if (! $this->confirm('¿Confirmás el migrate:fresh?', false)) {
                $this->info('Cancelado por el usuario. Backup disponible en: '.$rutaBackup);

                return 0;
            }
        }

        // 3. Ejecutar migrate:fresh
        $this->info('Paso 3/3: Ejecutando migrate:fresh...');
        $params = [];
        if ($this->option('seed')) {
            $params['--seed'] = true;
        }
        Artisan::call('migrate:fresh', $params, $this->output);

        $this->newLine();
        $this->info('migrate:fresh completado.');
        $this->info("Backup previo disponible en: {$rutaBackup}");

        return 0;
    }
}
