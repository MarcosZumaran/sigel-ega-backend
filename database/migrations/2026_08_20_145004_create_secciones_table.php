<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grados');
            $table->string('nombre', 10);
            $table->enum('turno', ['manana', 'tarde']);
            $table->integer('vacantes')->default(0);
            $table->foreignId('docente_id')->nullable()->constrained('docentes');
            $table->timestamps();

            $table->unique(['grado_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secciones');
    }
};