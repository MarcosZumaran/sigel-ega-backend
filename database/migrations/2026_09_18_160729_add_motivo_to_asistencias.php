<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->text('motivo_justificacion')->nullable()->after('estado');
            $table->string('archivo_justificacion', 500)->nullable()->after('motivo_justificacion');
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn(['motivo_justificacion', 'archivo_justificacion']);
        });
    }
};
