<?php

namespace Database\Factories;

use App\Models\Grado;
use App\Models\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seccion>
 */
class SeccionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grado_id' => Grado::factory(),
            'nombre' => 'T'.$this->faker->unique()->bothify('#?'),
            'turno' => 'manana',
            'vacantes' => 30,
            'docente_id' => null,
        ];
    }
}
