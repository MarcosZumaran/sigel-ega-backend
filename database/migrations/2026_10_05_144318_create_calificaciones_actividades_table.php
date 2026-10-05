<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->enum('nivel_logro', ['AD', 'A', 'B', 'C']);
            $table->timestamps();

            // Un alumno solo puede tener un nivel por actividad
            $table->unique(['actividad_id', 'matricula_id'], 'act_mat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones_actividades');
    }
};
