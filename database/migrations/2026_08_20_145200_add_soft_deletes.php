<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tablas de dominio que requieren soft delete (preservar integridad + restore admin-only) */
    private array $tablas = [
        'apoderados',
        'padres',
        'estudiantes',
        'matriculas',
        'calificaciones',
        'asistencias',
        'documentos',
        'periodos',
        'secciones',
        'docentes',
        'areas',
        'grados',
        'niveles',
        'roles',
        'estados',
        'tipos_matricula',
        'tipos_evaluacion',
        'tipos_documento',
        'parentescos',
        'configuraciones',
        'users',
    ];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            if (Schema::hasTable($tabla) && ! Schema::hasColumn($tabla, 'deleted_at')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->softDeletes()->after('updated_at');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'deleted_at')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
