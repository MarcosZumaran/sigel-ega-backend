<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matricula>
 */
class MatriculaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            'seccion_id' => Seccion::factory(),
            'periodo_id' => Periodo::factory(),
            'tipo_matricula_id' => 1,
            'tipo_vacante' => 'Regular',
            'fecha' => '2026-03-01',
            'estado_id' => null,
        ];
    }
}
