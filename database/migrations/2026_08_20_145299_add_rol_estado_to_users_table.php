<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'rol_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('rol_id')->nullable()->constrained('roles');
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'estado_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('estado_id')->nullable()->constrained('estados');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'rol_id')) {
                    $table->dropConstrainedForeignId('rol_id');
                }
                if (Schema::hasColumn('users', 'estado_id')) {
                    $table->dropConstrainedForeignId('estado_id');
                }
            });
        }
    }
};