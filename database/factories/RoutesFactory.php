<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Routes>
 */
class RoutesFactory extends Factory
{
    protected $model = \App\Models\Routes::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city() . ' Route',
            'index' => (string) fake()->unique()->numberBetween(1, 999),
            'type' => 'Normal',
            'time' => fake()->randomElement(['Morning', 'Evening']),
        ];
    }
}
