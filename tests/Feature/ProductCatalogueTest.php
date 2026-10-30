<?php

namespace Tests\Feature;

use App\Enums\ProductAvailability;
use App\Jobs\SendBackInStockAlerts;
use App\Livewire\ProductIndex;
use App\Livewire\StockAlertForm;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockAlert;
use App\Notifications\ProductBackInStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCatalogueTest extends TestCase
{
    use RefreshDatabase;

    private function catalogue(): void
    {
        $livestock = ProductCategory::factory()->create(['name' => 'Livestock', 'slug' => 'livestock']);
        $palm = ProductCategory::factory()->create(['name' => 'Palm Products', 'slug' => 'palm-products']);

        Product::factory()->for($livestock, 'category')->create(['name' => 'Broiler Chickens', 'slug' => 'broiler-chickens', 'summary' => 'Live or processed']);
        Product::factory()->for($livestock, 'category')->create(['name' => 'Market-Weight Cattle', 'slug' => 'cattle', 'availability' => ProductAvailability::OnRequest]);
        Product::factory()->for($palm, 'category')->create(['name' => 'Palm Oil', 'slug' => 'palm-oil', 'availability' => ProductAvailability::Seasonal]);
        Product::factory()->for($palm, 'category')->unpublished()->create(['name' => 'Hidden Palm Thing', 'slug' => 'hidden']);
    }

    public function test_lists_only_published_products(): void
    {
        $this->catalogue();

        Livewire::test(ProductIndex::class)
            ->assertSee('Broiler Chickens')->assertSee('Palm Oil')->assertDontSee('Hidden Palm Thing');
    }

    public function test_filters_by_category_search_and_availability(): void
    {
        $this->catalogue();

        Livewire::test(ProductIndex::class)
            ->call('setCategory', 'palm-products')
            ->assertSet('category', 'palm-products')
            ->assertSee('Palm Oil')->assertDontSee('Broiler Chickens');

        Livewire::test(ProductIndex::class)->set('search', 'processed')
            ->assertSee('Broiler Chickens')->assertDontSee('Palm Oil');

        Livewire::test(ProductIndex::class)->set('availability', 'on_request')
            ->assertSee('Market-Weight Cattle')->assertDontSee('Broiler Chickens');

        // Search wildcards are escaped.
        Livewire::test(ProductIndex::class)->set('search', '%')->assertSee('No products match your filters');
    }

    public function test_category_filter_is_read_from_the_url(): void
    {
        $this->catalogue();

        Livewire::withQueryParams(['category' => 'livestock'])->test(ProductIndex::class)
            ->assertSet('category', 'livestock')->assertSee('Broiler Chickens')->assertDontSee('Palm Oil');
    }

    public function test_empty_state_offers_to_clear_filters(): void
    {
        $this->catalogue();

        Livewire::test(ProductIndex::class)
            ->set('search', 'zebra')
            ->assertSee('No products match your filters')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSee('Broiler Chickens');
    }

    public function test_notify_me_subscribes_once_per_email(): void
    {
        RateLimiter::clear('stock-alert|127.0.0.1');
        $product = $this->makeProduct('Processed Poultry', ProductAvailability::OutOfSeason);

        Livewire::test(StockAlertForm::class, ['product' => $product])
            ->set('email', 'Buyer@Example.com')->call('subscribe')
            ->assertHasNoErrors()->assertSet('subscribed', true);
        Livewire::test(StockAlertForm::class, ['product' => $product])
            ->set('email', 'buyer@example.com')->call('subscribe')->assertSet('subscribed', true);

        $this->assertSame(1, StockAlert::count());
        $this->assertSame('buyer@example.com', StockAlert::sole()->email);

        Livewire::test(StockAlertForm::class, ['product' => $product])->set('email', 'nope')->call('subscribe')->assertHasErrors('email');
    }

    public function test_back_in_stock_alerts_are_sent_once_when_product_returns(): void
    {
        Notification::fake();
        $product = $this->makeProduct('Processed Poultry', ProductAvailability::OutOfSeason);
        StockAlert::factory()->for($product)->count(2)->create();
        $alreadyNotified = StockAlert::factory()->for($product)->create(['notified_at' => now()->subMonth()]);

        $product->update(['availability' => ProductAvailability::InStock]);

        Notification::assertSentOnDemandTimes(ProductBackInStock::class, 2);
        $this->assertSame(0, StockAlert::query()->pending()->count());

        // Re-running the job (e.g. a queue retry) sends nothing new.
        (new SendBackInStockAlerts($product))->handle();
        Notification::assertSentOnDemandTimes(ProductBackInStock::class, 2);
        $this->assertTrue($alreadyNotified->fresh()->notified_at->lt(now()->subWeek()));
    }

    public function test_alert_job_is_only_dispatched_when_leaving_out_of_season(): void
    {
        Queue::fake();
        $product = $this->makeProduct('Palm Kernels', ProductAvailability::Seasonal);

        $product->update(['availability' => ProductAvailability::InStock]);
        $product->update(['name' => 'Palm Kernels (bulk)']);
        Queue::assertNotPushed(SendBackInStockAlerts::class);

        $product->update(['availability' => ProductAvailability::OutOfSeason]);
        $product->update(['availability' => ProductAvailability::Seasonal]);
        Queue::assertPushed(SendBackInStockAlerts::class, 1);
    }
}
