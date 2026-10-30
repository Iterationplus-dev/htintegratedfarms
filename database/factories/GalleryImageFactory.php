<?php

namespace Database\Factories;

use App\Enums\GalleryCategory;
use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryImage>
 */
class GalleryImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'category' => fake()->randomElement(GalleryCategory::filterable()),
            'span' => null,
            'is_featured' => false,
            'is_published' => true,
            'sort' => fake()->numberBetween(0, 50),
        ];
    }
}
