<?php

namespace Database\Seeders;

use App\Models\Matricula;
use App\Services\AsistenciaService;
use App\Services\CalificacionService;
use App\Services\EstudianteService;
use App\Services\MatriculaService;
use App\Services\PadreService;
use Illuminate\Database\Seeder;

/**
 * Carga masiva de datos (~200 filas) para probar el data warehouse.
 * Idempotente por rangos de DNI: padres 71xxxxxx, estudiantes 81xxxxxx.
 * Uso: php artisan db:seed --class=DataWarehouseSeeder
 */
class DataWarehouseSeeder extends Seeder
{
    private const TOTAL_PADRES = 30;
    private const TOTAL_ESTUDIANTES = 50;
    private const TOTAL_CALIFICACIONES = 40;
    private const TOTAL_ASISTENCIAS = 30;

    private array $nombresM = ['Luis', 'Carlos', 'Miguel', 'Jorge', 'Diego', 'Andrés', 'Fernando', 'Raúl', 'Hugo', 'Pedro', 'César', 'Edwin', 'Marco', 'Julio', 'Renzo'];
    private array $nombresF = ['María', 'Rosa', 'Carmen', 'Lucía', 'Ana', 'Elena', 'Patricia', 'Julia', 'Teresa', 'Milagros', 'Vanessa', 'Karla', 'Diana', 'Yesenia', 'Fiorella'];
    private array $apellidos = ['Quispe', 'Huamán', 'Torres', 'Flores', 'Ramos', 'Vargas', 'Castillo', 'Mendoza', 'Aguilar', 'Rojas', 'Chávez', 'Salazar', 'Paredes', 'Cordero', 'Soto', 'Reyes', 'Cruz', 'Morales', 'Gutiérrez', 'Ponce'];

    public function run(): void
    {
        mt_srand(20260911);

        $padres = app(PadreService::class);
        $estudiantes = app(EstudianteService::class);
        $matriculas = app(MatriculaService::class);
        $notas = app(CalificacionService::class);
        $asistencias = app(AsistenciaService::class);

        // 1. Padres (auto-crean su hub apoderado)
        $padreIds = [];
        for ($i = 1; $i <= self::TOTAL_PADRES; $i++) {
            $fem = (bool) mt_rand(0, 1);
            $padre = $padres->create([
                'dni' => '71' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'nombres' => $fem ? $this->pick($this->nombresF) : $this->pick($this->nombresM),
                'apellidos' => $this->pick($this->apellidos) . ' ' . $this->pick($this->apellidos),
                'telefono' => '9' . str_pad((string) mt_rand(10000000, 99999999), 8, '0'),
            ]);
            $padreIds[] = $padre->id;
        }
        $this->command->info('Padres: ' . count($padreIds));

        // 2. Estudiantes (heredan apoderado del padre) + 3. Matrículas
        $areas = [1, 5, 10, 13, 19, 26, 30, 38];
        $matriculaIds = [];
        for ($i = 1; $i <= self::TOTAL_ESTUDIANTES; $i++) {
            $nivelId = $i % 2 === 0 ? 1 : 2; // mitad Primaria, mitad Secundaria
            $gradoId = $nivelId === 1 ? mt_rand(1, 6) : mt_rand(7, 11);
            $fem = (bool) mt_rand(0, 1);
            $est = $estudiantes->create([
                'dni' => '81' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'nombres' => $fem ? $this->pick($this->nombresF) : $this->pick($this->nombresM),
                'apellidos' => $this->pick($this->apellidos) . ' ' . $this->pick($this->apellidos),
                'fecha_nacimiento' => $this->fechaNacimiento($gradoId),
                'sexo' => $fem ? 'F' : 'M',
                'nivel_id' => $nivelId,
                'grado_id' => $gradoId,
                'padre_id' => $padreIds[($i - 1) % count($padreIds)],
            ]);

            // Matrícula en la sección del mismo grado (seccion.id == grado.id)
            $mat = $matriculas->create([
                'estudiante_id' => $est->id,
                'seccion_id' => $gradoId,
                'periodo_id' => 1,
                'tipo_matricula_id' => mt_rand(1, 2) === 1 ? 1 : 1,
                'fecha' => '2026-03-' . str_pad((string) mt_rand(16, 28), 2, '0', STR_PAD_LEFT),
                'estado_id' => 1,
            ]);
            $matriculaIds[] = ['id' => $mat->id, 'area' => $areas[$i % count($areas)]];
        }
        $this->command->info('Estudiantes+Matrículas: ' . count($matriculaIds));

        // 4. Calificaciones (mix vigesimal + literal CNEB)
        $niveles = ['AD', 'A', 'B'];
        $n = 0;
        foreach ($matriculaIds as $k => $m) {
            if ($n >= self::TOTAL_CALIFICACIONES) break;
            $literal = $k % 3 === 0;
            $notas->create([
                'matricula_id' => $m['id'],
                'area_id' => $m['area'],
                'tipo_evaluacion_id' => mt_rand(2, 3),
                'escala' => $literal ? 'literal' : 'vigesimal',
                'nota' => $literal ? null : mt_rand(10, 20),
                'nivel_logro' => $literal ? $niveles[$k % 3] : null,
            ]);
            $n++;
        }
        $this->command->info('Calificaciones: ' . $n);

        // 5. Asistencias (marzo-septiembre 2026, estados variados)
        $estados = ['presente', 'presente', 'presente', 'presente', 'tardia', 'ausente', 'justificado'];
        for ($i = 0; $i < self::TOTAL_ASISTENCIAS; $i++) {
            $m = $matriculaIds[$i % count($matriculaIds)];
            $mes = mt_rand(3, 9);
            $dia = $mes === 3 ? mt_rand(16, 28) : mt_rand(1, 28);
            $asistencias->create([
                'matricula_id' => $m['id'],
                'fecha' => '2026-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad((string) $dia, 2, '0', STR_PAD_LEFT),
                'estado' => $estados[$i % count($estados)],
            ]);
        }
        $this->command->info('Asistencias: ' . self::TOTAL_ASISTENCIAS);
    }

    private function pick(array $a): string
    {
        return $a[mt_rand(0, count($a) - 1)];
    }

    private function fechaNacimiento(int $gradoId): string
    {
        // Edad aproximada: Primaria 6-11, Secundaria 12-16 (dentro del rango 3-25)
        $edad = $gradoId <= 6 ? 5 + $gradoId : 6 + $gradoId;
        return date('Y-m-d', mktime(0, 0, 0, mt_rand(1, 12), mt_rand(1, 28), 2026 - $edad));
    }
}
