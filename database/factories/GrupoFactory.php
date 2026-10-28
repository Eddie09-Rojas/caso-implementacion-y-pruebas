<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Grupo>
 */
class GrupoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numeroCiclo' => fake()->numberBetween(1, 2),
            'ano' => fake()->numberBetween(2024, 2027),
            'numeroGrupo' => fake()->numberBetween(1, 5),
            'abierto' => true,
            'curso_id' => Curso::factory(),
        ];
    }
}
