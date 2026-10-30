<?php

namespace Database\Factories;

use App\Models\Stat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stat>
 */
class StatFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'page' => 'home',
            'value' => (string) fake()->numberBetween(10, 999),
            'label' => fake()->words(3, true),
            'short_label' => null,
            'sort' => 0,
        ];
    }
}
