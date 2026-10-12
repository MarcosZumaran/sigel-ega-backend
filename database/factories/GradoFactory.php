<?php

namespace Database\Factories;

use App\Models\Grado;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grado>
 */
class GradoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nivel_id' => Nivel::query()->inRandomOrder()->value('id') ?? 1,
            'nombre' => 'TEST-'.$this->faker->unique()->bothify('?#'),
        ];
    }
}
