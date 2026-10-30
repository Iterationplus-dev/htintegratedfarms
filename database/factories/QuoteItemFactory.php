<?php

namespace Database\Factories;

use App\Enums\QuoteFrequency;
use App\Models\Enquiry;
use App\Models\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteItem>
 */
class QuoteItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enquiry_id' => Enquiry::factory()->quote(),
            'product_id' => null,
            'product_name' => 'Table Eggs',
            'quantity' => fake()->numberBetween(10, 500),
            'unit' => 'crates',
            'frequency' => QuoteFrequency::Weekly,
        ];
    }
}
