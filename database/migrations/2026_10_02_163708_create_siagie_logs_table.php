<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siagie_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('operacion', ['import', 'export']);
            $table->string('archivo', 255)->nullable();
            $table->string('formato', 20)->nullable(); // xlsx, csv, pdf, json
            $table->foreignId('periodo_id')->nullable()
                  ->constrained('periodos')->nullOnDelete();
            $table->foreignId('seccion_id')->nullable()
                  ->constrained('secciones')->nullOnDelete();
            $table->unsignedInteger('filas_procesadas')->default(0);
            $table->unsignedInteger('filas_exitosas')->default(0);
            $table->unsignedInteger('filas_con_error')->default(0);
            $table->json('errores')->nullable();
            $table->enum('resultado', ['exitoso', 'parcial', 'fallido'])->default('exitoso');
            $table->unsignedInteger('duracion_ms')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['operacion', 'created_at']);
            $table->index('periodo_id');
            $table->index('seccion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siagie_logs');
    }
};
