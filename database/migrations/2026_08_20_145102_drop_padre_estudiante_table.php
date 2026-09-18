<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('padre_estudiante');
    }

    public function down(): void
    {
        Schema::create('padre_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('padre_id')->constrained('padres');
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('parentesco_id')->constrained('parentescos');
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
            $table->unique(['padre_id', 'estudiante_id', 'parentesco_id'], 'p_e_parentesco_unique');
        });
    }
};
