<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('padres', function (Blueprint $table) {
            $table->foreignId('apoderado_id')->nullable()->after('estado_id')->constrained('apoderados')->nullOnDelete();
            $table->index('apoderado_id');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->foreignId('apoderado_id')->nullable()->after('estado_id')->constrained('apoderados')->nullOnDelete();
            $table->index('apoderado_id');
        });
    }

    public function down(): void
    {
        Schema::table('padres', function (Blueprint $table) {
            $table->dropConstrainedForeignId('apoderado_id');
        });
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('apoderado_id');
        });
    }
};
