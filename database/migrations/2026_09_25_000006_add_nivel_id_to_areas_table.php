<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->foreignId('nivel_id')->nullable()->after('area_padre_id')->constrained('niveles')->nullOnDelete();
            $table->index('nivel_id');
        });

        // Núcleo compartido Primaria+Secundaria (NULL = ambos niveles)
        $comunes = ['Comunicación', 'Matemática', 'Ciencia y Tecnología', 'Educación Física', 'Arte y Cultura', 'Educación Religiosa', 'Inglés', 'Castellano como Segunda Lengua'];
        // Solo Primaria (en Secundaria se desdobla en Ciencias Sociales + DPCC)
        $primaria = ['Personal Social'];
        // Solo Secundaria
        $secundaria = ['Ciencias Sociales', 'Desarrollo Personal, Ciudadanía y Cívica', 'Educación para el Trabajo'];

        $primariaId = DB::table('niveles')->where('nombre', 'Primaria')->value('id');
        $secundariaId = DB::table('niveles')->where('nombre', 'Secundaria')->value('id');

        DB::table('areas')->whereIn('nombre', $primaria)->whereNull('area_padre_id')->update(['nivel_id' => $primariaId]);
        DB::table('areas')->whereIn('nombre', $secundaria)->whereNull('area_padre_id')->update(['nivel_id' => $secundariaId]);
        // $comunes y competencies hijas quedan NULL (heredan visibilidad total)
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nivel_id');
        });
    }
};
