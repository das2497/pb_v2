<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products>
 */
class ProductsFactory extends Factory
{
    protected $model = \App\Models\Products::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 50, 500);

        return [
            'item_number' => 'ITM-' . fake()->unique()->numerify('#####'),
            'name_english' => fake()->words(3, true),
            'name_sinhala' => fake()->words(3, true),
            'visibility' => 'All',
            'order_time' => 'All',
            'category' => 'CAKE ITEMS',
            'unit_price' => (string) $unitPrice,
            'mrp' => (string) round($unitPrice * 1.1, 2),
            'direct_sale_price' => (string) round($unitPrice * 0.9, 2),
            'img' => 'placeholder.png',
        ];
    }
}
