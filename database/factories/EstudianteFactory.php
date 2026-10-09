<?php

namespace Database\Factories;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo_estudiante' => 'T'.$this->faker->unique()->numerify('#########'),
            'dni' => $this->faker->unique()->numerify('########'),
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'fecha_nacimiento' => '2010-01-01',
            'sexo' => 'M',
            'nivel_id' => null,
            'grado_id' => null,
            'estado_id' => null,
            'apoderado_id' => null,
        ];
    }
}
