<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RepAssignShop>
 */
class RepAssignShopFactory extends Factory
{
    protected $model = \App\Models\RepAssignShop::class;

    public function definition(): array
    {
        return [
            'rep_id' => (string) fake()->unique()->numberBetween(1, 1000),
            'shop_id' => (string) fake()->unique()->numberBetween(1, 1000),
        ];
    }
}
