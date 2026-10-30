<?php

namespace Database\Factories;

use App\Enums\ProductAvailability;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'product_category_id' => ProductCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'summary' => fake()->sentence(8),
            'featured_summary' => null,
            'description' => fake()->paragraph(),
            'specs' => [['label' => 'Packaging', 'value' => 'Crate']],
            'availability' => ProductAvailability::InStock,
            'unit' => 'crates',
            'is_featured' => false,
            'is_published' => true,
            'sort' => fake()->numberBetween(0, 20),
        ];
    }

    public function outOfSeason(): static
    {
        return $this->state(['availability' => ProductAvailability::OutOfSeason]);
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
