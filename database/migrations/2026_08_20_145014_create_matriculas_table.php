<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->foreignId('periodo_id')->constrained('periodos');
            $table->foreignId('tipo_matricula_id')->constrained('tipos_matricula');
            $table->date('fecha')->nullable();
            $table->foreignId('estado_id')->nullable()->constrained('estados');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['estudiante_id', 'periodo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};