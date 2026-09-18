<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // matriculas: reportes por periodo+seccion (índice compuesto) + estudiante ya tiene unique
        Schema::table('matriculas', function (Blueprint $table) {
            $table->index(['periodo_id', 'seccion_id'], 'idx_matriculas_periodo_seccion');
            $table->index(['estudiante_id', 'periodo_id'], 'idx_matriculas_est_periodo');
        });
        // calificaciones: notas por matricula+area+tipo (ya unique) + índice para promedio por area
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->index(['area_id', 'tipo_evaluacion_id'], 'idx_calif_area_tipo');
        });
        // asistencias: rango por fecha y conteo por matricula
        Schema::table('asistencias', function (Blueprint $table) {
            $table->index(['fecha'], 'idx_asistencias_fecha');
        });
        // apoderados/padres/estudiantes: búsqueda por nombre/apellidos
        // FULLTEXT solo en MyISAM/InnoDB con utf8mb4 (MariaDB 10.4+ soporta)
        try {
            DB::statement('ALTER TABLE estudiantes ADD FULLTEXT ft_estudiantes_nombre (nombres, apellidos)');
            DB::statement('ALTER TABLE padres ADD FULLTEXT ft_padres_nombre (nombres, apellidos)');
            DB::statement('ALTER TABLE apoderados ADD FULLTEXT ft_apoderados_nombre (nombres, apellidos)');
        } catch (\Throwable $e) {
            // fallback a índice normal si el motor no soporta FULLTEXT
            Schema::table('estudiantes', fn (Blueprint $t) => $t->index(['apellidos', 'nombres'], 'idx_estudiantes_apellidos_nombres'));
            Schema::table('padres', fn (Blueprint $t) => $t->index(['apellidos', 'nombres'], 'idx_padres_apellidos_nombres'));
            Schema::table('apoderados', fn (Blueprint $t) => $t->index(['apellidos', 'nombres'], 'idx_apoderados_apellidos_nombres'));
        }
        Schema::table('documentos', function (Blueprint $table) {
            $table->index(['tipo_documento_id', 'fecha'], 'idx_docs_tipo_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropIndex('idx_matriculas_periodo_seccion');
            $table->dropIndex('idx_matriculas_est_periodo');
        });
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropIndex('idx_calif_area_tipo');
        });
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropIndex('idx_asistencias_fecha');
        });
        try { DB::statement('ALTER TABLE estudiantes DROP INDEX ft_estudiantes_nombre'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE padres DROP INDEX ft_padres_nombre'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE apoderados DROP INDEX ft_apoderados_nombre'); } catch (\Throwable $e) {}
        try { Schema::table('estudiantes', fn (Blueprint $t) => $t->dropIndex('idx_estudiantes_apellidos_nombres')); } catch (\Throwable $e) {}
        try { Schema::table('padres', fn (Blueprint $t) => $t->dropIndex('idx_padres_apellidos_nombres')); } catch (\Throwable $e) {}
        try { Schema::table('apoderados', fn (Blueprint $t) => $t->dropIndex('idx_apoderados_apellidos_nombres')); } catch (\Throwable $e) {}
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropIndex('idx_docs_tipo_fecha');
        });
    }
};
