<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_documento_id')
                ->constrained('tipos_documento')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->unsignedInteger('ultimo_numero')->default(0);
            $table->string('formato', 100)
                ->default('{tipo} N° {numero:03d}-{anio}-IE-EGA');
            $table->timestamps();

            // Un correlativo por tipo + año
            $table->unique(['tipo_documento_id', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlativos');
    }
};
