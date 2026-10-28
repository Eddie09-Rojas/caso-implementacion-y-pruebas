<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Curso>
 */
class CursoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sigla' => fake()->unique()->bothify('IF####'),
            'nombreCurso' => fake()->unique()->words(3, true),
            'creditos' => fake()->numberBetween(1, 6),
        ];
    }
}
