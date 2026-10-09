<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product_category>
 */
class Product_categoryFactory extends Factory
{
    protected $model = \App\Models\Product_category::class;

    public function definition(): array
    {
        return [
            'category' => fake()->unique()->word(),
            'main_category' => 'General',
        ];
    }
}
