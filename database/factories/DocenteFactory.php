<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Docente>
 */
class DocenteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codEmpleado' => fake()->unique()->numberBetween(1000, 999999),
            'nombreEmpleado' => fake()->firstName(),
            'apellidosEmpleado' => fake()->lastName() . ' ' . fake()->lastName(),
        ];
    }
}
