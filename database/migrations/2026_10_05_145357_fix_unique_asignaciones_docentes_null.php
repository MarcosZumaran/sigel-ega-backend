<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La FK docente_id usa el unique como índice (no tiene índice propio);
        // hay que soltarla antes de tocar el unique (error MySQL 1553).
        DB::statement('ALTER TABLE asignaciones_docentes DROP FOREIGN KEY asignaciones_docentes_docente_id_foreign');

        // Eliminar unique actual
        DB::statement('ALTER TABLE asignaciones_docentes DROP INDEX asig_doc_sec_area_unique');

        // Agregar columna generada (MariaDB 10.2+ / MySQL 5.7+)
        DB::statement('
            ALTER TABLE asignaciones_docentes
            ADD COLUMN area_id_safe BIGINT UNSIGNED
            GENERATED ALWAYS AS (COALESCE(area_id, 0)) STORED
        ');

        // Nuevo unique incluyendo la columna segura
        // (docente_id queda a la izquierda, así la FK vuelve a tener índice)
        DB::statement('
            ALTER TABLE asignaciones_docentes
            ADD UNIQUE KEY asig_doc_sec_area_unique (docente_id, seccion_id, area_id_safe)
        ');

        // Restaurar FK original (CASCADE, según migración de creación)
        DB::statement('ALTER TABLE asignaciones_docentes ADD CONSTRAINT asignaciones_docentes_docente_id_foreign FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE asignaciones_docentes DROP FOREIGN KEY asignaciones_docentes_docente_id_foreign');
        DB::statement('ALTER TABLE asignaciones_docentes DROP INDEX asig_doc_sec_area_unique');
        DB::statement('ALTER TABLE asignaciones_docentes DROP COLUMN area_id_safe');
        DB::statement('
            ALTER TABLE asignaciones_docentes
            ADD UNIQUE KEY asig_doc_sec_area_unique (docente_id, seccion_id, area_id)
        ');
        DB::statement('ALTER TABLE asignaciones_docentes ADD CONSTRAINT asignaciones_docentes_docente_id_foreign FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE');
    }
};
