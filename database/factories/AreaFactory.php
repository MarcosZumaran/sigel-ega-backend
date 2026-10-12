<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Competencia '.$this->faker->words(2, true),
            'tipo' => 'competencia',
            'area_padre_id' => null,
            'nivel_id' => null,
        ];
    }
}
