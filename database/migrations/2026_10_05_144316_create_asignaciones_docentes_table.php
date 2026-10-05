<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docentes')->cascadeOnDelete();
            $table->foreignId('seccion_id')->constrained('secciones')->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->cascadeOnDelete();
            // area_id NULL = docente encargado de toda la sección (primaria)
            // area_id NO NULL = docente que enseña esa área en esa sección (secundaria)
            $table->boolean('es_encargado')->default(false);
            // es_encargado = true → docente tutor/encargado de la sección
            $table->timestamps();
            $table->softDeletes();

            // Un docente no puede estar dos veces en la misma combinación
            $table->unique(
                ['docente_id', 'seccion_id', 'area_id'],
                'asig_doc_sec_area_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_docentes');
    }
};
