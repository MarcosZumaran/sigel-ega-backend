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
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->enum('nivel_logro', ['AD','A','B','C'])->nullable()->after('nota')->comment('CNEB literal: AD destacado, A logrado, B proceso, C inicio');
            $table->enum('escala', ['literal','vigesimal'])->default('literal')->after('nivel_logro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropColumn(['nivel_logro','escala']);
        });
    }
};
