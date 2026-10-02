<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 0. La FK calificaciones_matricula_id_foreign usa mat_area_ev_unique
        // como índice de soporte (error 1553 si se elimina el índice primero).
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropForeign('calificaciones_matricula_id_foreign');
        });

        // 1. Eliminar el índice antiguo (sin bimestre)
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropUnique('mat_area_ev_unique');
        });

        // 2. Crear el nuevo índice incluyendo bimestre_id
        // Nota: bimestre_id es nullable. MySQL permite múltiples NULL en unique,
        // así que las filas con bimestre_id=NULL no quedan restringidas.
        // En la práctica, todas las calificaciones nuevas deben tener bimestre_id.
        // El nuevo índice (leftmost matricula_id) vuelve a dar soporte a la FK.
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->unique(
                ['matricula_id', 'area_id', 'tipo_evaluacion_id', 'bimestre_id'],
                'mat_area_ev_bim_unique'
            );
        });

        // 2b. Restaurar la FK eliminada en el paso 0 (original: RESTRICT, sin cascade).
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->foreign('matricula_id', 'calificaciones_matricula_id_foreign')
                ->references('id')->on('matriculas');
        });

        // 3. Backfill de seguridad: si hay calificaciones sin bimestre,
        //    asignarles el primer bimestre del periodo de su matrícula.
        DB::statement('
            UPDATE calificaciones c
            INNER JOIN matriculas m ON c.matricula_id = m.id
            INNER JOIN bimestres b ON b.periodo_id = m.periodo_id AND b.numero = 1
            SET c.bimestre_id = b.id
            WHERE c.bimestre_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropForeign('calificaciones_matricula_id_foreign');
            $table->dropUnique('mat_area_ev_bim_unique');
            $table->unique(
                ['matricula_id', 'area_id', 'tipo_evaluacion_id'],
                'mat_area_ev_unique'
            );
            $table->foreign('matricula_id', 'calificaciones_matricula_id_foreign')
                ->references('id')->on('matriculas');
        });
    }
};
