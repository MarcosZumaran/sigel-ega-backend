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
        Schema::table('estudiantes', function (Blueprint $table) {
            // Campos FUM oficiales
            $table->string('lengua_materna', 50)->nullable()
                  ->after('sexo')
                  ->comment('Lengua materna del estudiante (FUM)');
            $table->string('autoidentificacion_etnica', 100)->nullable()
                  ->after('lengua_materna')
                  ->comment('Autoidentificación étnica (FUM)');

            // Discapacidad (complementa necesidades_especiales)
            $table->boolean('tiene_discapacidad')->default(false)
                  ->after('autoidentificacion_etnica');
            $table->string('tipo_discapacidad', 100)->nullable()
                  ->after('tiene_discapacidad');
            $table->string('grado_discapacidad', 50)->nullable()
                  ->after('tipo_discapacidad')
                  ->comment('Leve, Moderada, Severa');
            $table->boolean('tiene_certificado_discapacidad')->default(false)
                  ->after('grado_discapacidad');

            // Datos complementarios FUM
            $table->string('pais_nacimiento', 50)->default('Perú')
                  ->after('fecha_nacimiento');
            $table->string('departamento_nacimiento', 100)->nullable()
                  ->after('pais_nacimiento');
            $table->string('provincia_nacimiento', 100)->nullable()
                  ->after('departamento_nacimiento');
            $table->string('distrito_nacimiento', 100)->nullable()
                  ->after('provincia_nacimiento');
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn([
                'lengua_materna',
                'autoidentificacion_etnica',
                'tiene_discapacidad',
                'tipo_discapacidad',
                'grado_discapacidad',
                'tiene_certificado_discapacidad',
                'pais_nacimiento',
                'departamento_nacimiento',
                'provincia_nacimiento',
                'distrito_nacimiento',
            ]);
        });
    }
};
