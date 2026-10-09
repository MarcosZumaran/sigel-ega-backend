<?php

namespace Database\Factories;

use App\Models\Actividad;
use App\Models\Area;
use App\Models\Bimestre;
use App\Models\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Actividad>
 */
class ActividadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'competencia_id' => Area::factory()->state(['tipo' => 'competencia']),
            'bimestre_id' => Bimestre::factory(),
            'seccion_id' => Seccion::factory(),
            'titulo' => 'Actividad '.$this->faker->words(2, true),
            'fecha' => '2026-10-15',
            'creado_por' => null,
        ];
    }
}
