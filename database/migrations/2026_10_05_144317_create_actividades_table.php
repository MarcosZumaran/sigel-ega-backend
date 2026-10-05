<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competencia_id')->constrained('areas')->cascadeOnDelete();
            // competencia_id = area con tipo='competencia'
            $table->foreignId('bimestre_id')->constrained('bimestres')->cascadeOnDelete();
            $table->foreignId('seccion_id')->constrained('secciones')->cascadeOnDelete();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->foreignId('creado_por')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['competencia_id', 'bimestre_id', 'seccion_id']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
