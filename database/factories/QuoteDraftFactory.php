<?php

namespace Database\Factories;

use App\Models\QuoteDraft;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QuoteDraft>
 */
class QuoteDraftFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'email' => fake()->safeEmail(),
            'data' => ['items' => [], 'deliveryLocation' => 'Lagos', 'step' => 2],
            'expires_at' => now()->addDays(14),
        ];
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }
}
