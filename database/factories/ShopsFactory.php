<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shops>
 */
class ShopsFactory extends Factory
{
    protected $model = \App\Models\Shops::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'sinhala_name' => fake()->company(),
            'branch_code' => 'BC-' . fake()->unique()->numerify('####'),
            'email' => fake()->unique()->safeEmail(),
            'contact' => fake()->numerify('07########'),
            'price_range' => fake()->randomElement(['Unit Price', 'PB MRP', 'PB Direct Sale Price']),
            'order_time' => 'All',
            'morning_route' => fake()->word(),
            'evening_route' => fake()->word(),
            'type' => 'Normal',
        ];
    }
}
