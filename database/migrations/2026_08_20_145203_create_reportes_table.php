<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50)->comment('auxiliar, asistencia, diagnostica, refuerzo, nominas, etc');
            $table->foreignId('periodo_id')->nullable()->constrained('periodos')->nullOnDelete();
            $table->foreignId('seccion_id')->nullable()->constrained('secciones')->nullOnDelete();
            $table->string('formato', 10)->default('pdf')->comment('pdf, excel, csv');
            $table->json('parametros')->nullable()->comment('filtros flexibles futuros');
            $table->string('estado', 20)->default('generado')->comment('generado, cacheado, expirado');
            $table->string('ruta_archivo', 500)->nullable();
            $table->string('hash', 64)->nullable();
            $table->unsignedBigInteger('generado_por')->nullable();
            $table->foreign('generado_por')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('expira_en')->nullable()->comment('para listar 1 año por defecto');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tipo', 'periodo_id']);
            $table->index(['seccion_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
