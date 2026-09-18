<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_id')->constrained('personal')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_entrada')->nullable()->comment('entrada, salida queda nullable para 2da encuesta');
            $table->time('hora_salida')->nullable();
            $table->enum('estado', ['presente', 'ausente', 'tardia', 'justificado', 'permiso', 'comision', 'vacaciones'])->default('presente');
            $table->text('descripcion_justificacion')->nullable()->comment('híbrida: texto libre');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete()->comment('quien marcó: propio o admin/directivo');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['personal_id', 'fecha'], 'uniq_personal_fecha');
            $table->index(['fecha'], 'idx_ap_fecha');
            $table->index(['personal_id', 'fecha'], 'idx_ap_personal_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_personal');
    }
};
