<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar apoderados que no tienen padres ni estudiantes vinculados
        $deleted = DB::table('apoderados')
            ->whereNull('deleted_at')
            ->whereNotIn('id', function ($q) {
                $q->select('apoderado_id')->from('padres')
                  ->whereNotNull('apoderado_id')
                  ->whereNull('deleted_at');
            })
            ->whereNotIn('id', function ($q) {
                $q->select('apoderado_id')->from('estudiantes')
                  ->whereNotNull('apoderado_id')
                  ->whereNull('deleted_at');
            })
            ->update(['deleted_at' => now()]);

        \Log::info("[migration] cleanup_huerfanos_apoderados: {$deleted} apoderados marcados como eliminados");
    }

    public function down(): void
    {
        // No-op. Revertir requeriría saber cuáles eran los originales.
        // En caso de emergencia, restaurar desde el backup.
    }
};
