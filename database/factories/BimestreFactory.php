<?php

namespace Database\Factories;

use App\Models\Bimestre;
use App\Models\Periodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bimestre>
 */
class BimestreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'periodo_id' => Periodo::factory(),
            'numero' => 1,
            'nombre' => 'Bimestre '.$this->faker->numberBetween(1, 4),
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-05-15',
            'activo' => true,
        ];
    }
}
