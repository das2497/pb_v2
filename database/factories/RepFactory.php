<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rep>
 */
class RepFactory extends Factory
{
    protected $model = \App\Models\Rep::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'contact' => fake()->numerify('07########'),
            'type' => 'Normal',
            'access' => 'on',
            'password' => fake()->regexify('[A-Za-z0-9]{20}'),
        ];
    }
}
