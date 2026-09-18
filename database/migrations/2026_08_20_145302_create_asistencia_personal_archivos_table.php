<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_personal_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')->constrained('asistencias_personal')->cascadeOnDelete();
            $table->string('ruta_archivo', 500);
            $table->string('nombre_original', 200);
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('tamano')->nullable()->comment('bytes');
            $table->timestamps();
            $table->index(['asistencia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_personal_archivos');
    }
};
