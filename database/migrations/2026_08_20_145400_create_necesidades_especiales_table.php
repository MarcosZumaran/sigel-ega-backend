<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('necesidades_especiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete()->cascadeOnUpdate();
            // Clasificación MINEDU: SAANEE / SEHO / SAE según RM 531-2021, RVM 041-2024
            $table->enum('servicio', ['SAANEE', 'SEHO', 'SAE', 'OTRO'])->default('SAANEE')->comment('SAANEE=apoyo NEE discapacidad, SEHO=hospitalario, SAE=apoyo educativo');
            $table->string('tipo_nee', 150)->comment('Ej: Discapacidad intelectual leve, TEA, discapacidad visual, talento/sobredotación, enfermedad crónica');
            $table->text('descripcion')->nullable();
            // Docs entrevista EGA: certificado salud + registro solicitud
            $table->string('certificado_salud', 500)->nullable()->comment('Ruta archivo certificado de salud');
            $table->string('registro_solicitud', 150)->nullable()->comment('N° registro de solicitud - entrevista EGA');
            $table->enum('estado_seho', ['pendiente', 'en_atencion', 'atendido', 'reincorporado'])->nullable()->comment('Estado SEHO entrevista');
            $table->string('codigo_seho', 100)->nullable()->comment('Código de registro SEHO');
            $table->string('nombre_seho', 200)->nullable()->comment('Nombre del SEHO/hospital o CREBE');
            $table->date('fecha_solicitud')->nullable();
            $table->date('fecha_reincorporacion')->nullable()->comment('Fecha de reincorporación a la IE');
            $table->boolean('reincorporado')->default(false);
            // Apoyo inclusivo
            $table->string('poi_ruta', 500)->nullable()->comment('Plan de Orientación Individual POI RM 531-2021');
            $table->string('evaluacion_psicopedagogica_ruta', 500)->nullable();
            $table->text('ajustes_razonables')->nullable()->comment('Adaptaciones curriculares RM 041-2024');
            $table->string('docente_sanee', 200)->nullable()->comment('Docente SAANEE/SAE asignado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('estudiante_id');
            $table->index(['servicio', 'estado_seho']);
            $table->index('codigo_seho');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('necesidades_especiales');
    }
};
