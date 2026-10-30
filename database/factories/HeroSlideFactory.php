<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSlide>
 */
class HeroSlideFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eyebrow' => 'Poultry Production',
            'title' => fake()->sentence(5),
            'body' => fake()->sentence(14),
            'image_alt' => fake()->sentence(4),
            'image_position' => '50% 50%',
            'primary_cta_label' => 'Explore Our Farm',
            'primary_cta_url' => '/about',
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
