<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conclusiones_descriptivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->foreignId('competencia_id')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('bimestre_id')->constrained('bimestres')->cascadeOnDelete();
            $table->text('texto')->nullable();
            $table->timestamps();
            $table->unique(['matricula_id', 'competencia_id', 'bimestre_id'], 'conc_desc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conclusiones_descriptivas');
    }
};
