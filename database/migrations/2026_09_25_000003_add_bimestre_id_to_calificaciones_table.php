<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->foreignId('bimestre_id')->nullable()->after('tipo_evaluacion_id')
                ->constrained('bimestres')->nullOnDelete();
            $table->index('bimestre_id');
        });
    }

    public function down(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bimestre_id');
        });
    }
};
