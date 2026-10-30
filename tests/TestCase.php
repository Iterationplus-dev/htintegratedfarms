<?php

namespace Tests;

use App\Enums\ProductAvailability;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\RateLimiter;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Page tests do not depend on a compiled Vite manifest.
        $this->withoutVite();
        RateLimiter::clear('enquiry:contact|127.0.0.1');
    }

    /** A published, quotable product in a named category. */
    protected function makeProduct(string $name = 'Table Eggs', ProductAvailability $availability = ProductAvailability::InStock, array $attributes = []): Product
    {
        $category = ProductCategory::query()->firstOrCreate(['slug' => 'farm-produce'], ['name' => 'Farm Produce', 'short_name' => 'Produce']);

        return Product::factory()->for($category, 'category')->create(array_merge([
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'availability' => $availability,
            'unit' => 'crates',
        ], $attributes));
    }
}
