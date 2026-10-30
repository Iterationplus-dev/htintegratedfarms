<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAlert>
 */
class StockAlertFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->outOfSeason(),
            'email' => fake()->unique()->safeEmail(),
            'notified_at' => null,
        ];
    }
}
