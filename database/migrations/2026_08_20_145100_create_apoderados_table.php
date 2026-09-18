<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apoderados', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->nullable()->unique();
            $table->string('dni', 8)->nullable()->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('parentesco', 50)->nullable()->comment('Parentesco principal con los estudiantes: Padre/Madre/Apoderado/Tutor');
            $table->foreignId('estado_id')->nullable()->constrained('estados')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apoderados');
    }
};
