<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('tipo_evaluacion_id')->constrained('tipos_evaluacion');
            $table->decimal('nota', 5, 2)->nullable();
            $table->boolean('es_nota_c')->default(false);
            $table->string('motivo_nota_c', 255)->nullable();
            $table->timestamps();

            $table->unique(['matricula_id', 'area_id', 'tipo_evaluacion_id'], 'mat_area_ev_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};