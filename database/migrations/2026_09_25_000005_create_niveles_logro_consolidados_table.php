<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveles_logro_consolidados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->foreignId('competencia_id')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('bimestre_id')->constrained('bimestres')->cascadeOnDelete();
            $table->enum('nivel', ['AD', 'A', 'B', 'C']);
            $table->boolean('es_final')->default(false);
            $table->timestamps();
            $table->unique(['matricula_id', 'competencia_id', 'bimestre_id', 'es_final'], 'niv_logro_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles_logro_consolidados');
    }
};
