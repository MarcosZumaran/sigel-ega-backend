<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bimestres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('periodos')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero');
            $table->string('nombre', 100);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['periodo_id', 'numero']);
            $table->index(['periodo_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bimestres');
    }
};
