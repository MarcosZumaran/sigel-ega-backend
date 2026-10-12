<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use ZipArchive;

class SigelRestore extends Command
{
    protected $signature = 'sigel:restore
                            {archivo : Ruta del .zip a restaurar}
                            {--force : No pedir confirmación}
                            {--solo-bd : Solo restaurar la base de datos}';

    protected $description = 'Restaura un backup de SIGEL-EGA (REEMPLAZA los datos actuales)';

    public function handle(): int
    {
        $archivo = $this->argument('archivo');

        if (! file_exists($archivo)) {
            $this->error("Archivo no encontrado: {$archivo}");
            return 1;
        }
        if (! str_ends_with(strtolower($archivo), '.zip')) {
            $this->error('El archivo debe ser .zip');
            return 1;
        }
        if (filesize($archivo) < 1024) {
            $this->error('El archivo está vacío o corrupto.');
            return 1;
        }

        if (! $this->option('force')) {
            $this->warn('ADVERTENCIA: Esto REEMPLAZARA la base de datos actual.');
            $this->warn("   Archivo: {$archivo}");
            $this->warn('   Tamaño: ' . round(filesize($archivo) / 1024 / 1024, 2) . ' MB');
            $this->warn('   Sugerencia: correr sigel:backup antes de continuar.');
            if (! $this->confirm('¿Continuar?', false)) {
                $this->info('Cancelado.');
                return 0;
            }
        }

        $tmp = storage_path('app/restore-' . uniqid());
        mkdir($tmp, 0755, true);

        try {
            $zip = new ZipArchive();
            if ($zip->open($archivo) !== true) {
                throw new \RuntimeException('No se pudo abrir el ZIP.');
            }
            $zip->extractTo($tmp);
            $zip->close();

            $sqlFiles = glob($tmp . '/db-dumps/*.sql') ?: [];
            if (empty($sqlFiles)) {
                throw new \RuntimeException('No se encontró dump SQL en el backup.');
            }
            $sqlFile = $sqlFiles[0];
            $this->info('Dump encontrado: ' . basename($sqlFile));

            $db = config('database.connections.' . config('database.default'));

            $cmd = sprintf(
                'mysql -h %s -P %s -u %s %s < %s',
                escapeshellarg($db['host']),
                escapeshellarg($db['port']),
                escapeshellarg($db['username']),
                escapeshellarg($db['database']),
                escapeshellarg($sqlFile)
            );

            $result = Process::env(['MYSQL_PWD' => $db['password']])
                ->timeout(600)
                ->run($cmd);

            if (! $result->successful()) {
                throw new \RuntimeException('mysql CLI falló: ' . $result->errorOutput());
            }

            $this->info('Base de datos restaurada correctamente.');

            if (! $this->option('solo-bd')) {
                $this->info('Restauración de archivos no implementada todavía.');
            }

            Log::info('sigel:restore completado', ['archivo' => $archivo]);
            return 0;

        } catch (\Throwable $e) {
            $this->error('Restauración falló: ' . $e->getMessage());
            Log::error('sigel:restore falló', ['error' => $e->getMessage()]);
            return 1;

        } finally {
            if (is_dir($tmp)) {
                foreach (glob($tmp . '/db-dumps/*') ?: [] as $f) {
                    @unlink($f);
                }
                foreach (glob($tmp . '/*') ?: [] as $f) {
                    is_dir($f) ? @rmdir($f) : @unlink($f);
                }
                @rmdir($tmp);
            }
        }
    }
}
