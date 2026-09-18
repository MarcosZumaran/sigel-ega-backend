<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 20); // create, update, delete, restore, login, logout
            $table->string('tabla', 50);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('payload')->nullable()->comment('cambios o snapshot JSON');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('origen', 100)->nullable()->comment('api / web / seeder / console');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tabla', 'registro_id']);
            $table->index('usuario_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
