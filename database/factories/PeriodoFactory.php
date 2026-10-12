<?php

namespace Database\Factories;

use App\Models\Periodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Periodo>
 */
class PeriodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'TEST-'.$this->faker->unique()->numerify('####'),
            'anio' => 2026,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-12-15',
            'activo' => true,
        ];
    }
}
