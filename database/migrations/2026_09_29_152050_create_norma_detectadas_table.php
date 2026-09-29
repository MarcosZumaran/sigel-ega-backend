<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('normas_detectadas', function (Blueprint $table) {
            $table->id();
            $table->integer('anio')->unique();
            $table->string('url_pdf', 500)->nullable();
            $table->json('fechas_json')->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('normas_detectadas');
    }
};
