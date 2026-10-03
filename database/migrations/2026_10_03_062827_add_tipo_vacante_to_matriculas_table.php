<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            // RM 193-2020-MINEDU: Regular, Ampliada, Virtual
            $table->enum('tipo_vacante', ['Regular', 'Ampliada', 'Virtual'])
                  ->default('Regular')
                  ->after('tipo_matricula_id')
                  ->comment('RM N° 193-2020-MINEDU');
        });
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn('tipo_vacante');
        });
    }
};
