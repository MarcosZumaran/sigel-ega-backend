<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calificaciones_actividades', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('matricula_id', 'cal_act_matricula_idx');
            $table->index('nivel_logro', 'cal_act_nivel_idx');
        });
    }

    public function down(): void
    {
        Schema::table('calificaciones_actividades', function (Blueprint $table) {
            $table->dropIndex('cal_act_matricula_idx');
            $table->dropIndex('cal_act_nivel_idx');
            $table->dropSoftDeletes();
        });
    }
};
