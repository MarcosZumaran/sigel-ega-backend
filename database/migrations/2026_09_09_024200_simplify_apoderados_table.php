<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simplifica apoderados a nodo hub: solo id + uuid + timestamps + soft deletes.
     * Los datos personales viven en padres y estudiantes.
     */
    public function up(): void
    {
        Schema::table('apoderados', function (Blueprint $table) {
            // FK hacia estados (nullable, nullOnDelete en la creación original)
            try {
                $table->dropForeign(['estado_id']);
            } catch (\Throwable) {
            }

            // Índices únicos de la estructura anterior
            foreach (['apoderados_codigo_unique', 'apoderados_dni_unique'] as $index) {
                try {
                    $table->dropUnique($index);
                } catch (\Throwable) {
                }
            }

            // Columnas redundantes: los datos personales ya están en padres/estudiantes
            $columnas = [
                'codigo',
                'dni',
                'nombres',
                'apellidos',
                'telefono',
                'email',
                'direccion',
                'parentesco',
                'estado_id',
                'observaciones',
            ];
            $existentes = array_values(array_filter(
                $columnas,
                fn (string $col) => Schema::hasColumn('apoderados', $col)
            ));

            if ($existentes !== []) {
                $table->dropColumn($existentes);
            }

            if (! Schema::hasColumn('apoderados', 'uuid')) {
                $table->string('uuid', 36)->nullable()->unique()->after('id');
            }
        });
    }

    /**
     * Revierte la simplificación (restaura la estructura anterior, sin datos).
     */
    public function down(): void
    {
        Schema::table('apoderados', function (Blueprint $table) {
            try {
                $table->dropUnique(['uuid']);
            } catch (\Throwable) {
            }

            if (Schema::hasColumn('apoderados', 'uuid')) {
                $table->dropColumn('uuid');
            }

            $table->string('codigo', 30)->nullable()->unique();
            $table->string('dni', 8)->nullable()->unique();
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('parentesco', 50)->nullable();
            $table->foreignId('estado_id')->nullable()->constrained('estados')->nullOnDelete();
            $table->text('observaciones')->nullable();
        });
    }
};
