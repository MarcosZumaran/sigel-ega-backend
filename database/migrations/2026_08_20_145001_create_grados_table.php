<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_id')->constrained('niveles');
            $table->string('nombre');
            $table->timestamps();

            $table->unique(['nivel_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grados');
    }
};