<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = \App\Models\Cart::class;

    public function definition(): array
    {
        return [
            'item_number' => 'ITM-' . fake()->unique()->numerify('#####'),
            'qty' => (string) fake()->numberBetween(1, 10),
            'price' => (string) fake()->randomFloat(2, 50, 500),
            'remarke' => null,
            'shop_bc_number' => 'BC-' . fake()->unique()->numerify('####'),
            'order_number' => null,
            'rep' => null,
            'default_name' => null,
        ];
    }
}
