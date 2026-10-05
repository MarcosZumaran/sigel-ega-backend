<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
        });
        // MODIFY directo: evita requerir doctrine/dbal para ->change()
        DB::statement('ALTER TABLE actividades MODIFY creado_por BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE actividades ADD CONSTRAINT actividades_creado_por_foreign FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE actividades DROP FOREIGN KEY actividades_creado_por_foreign');
        DB::statement('ALTER TABLE actividades MODIFY creado_por BIGINT UNSIGNED NOT NULL');
        Schema::table('actividades', function (Blueprint $table) {
            $table->foreign('creado_por')->references('id')->on('users');
        });
    }
};
