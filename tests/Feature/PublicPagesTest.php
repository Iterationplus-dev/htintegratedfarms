<?php

namespace Tests\Feature;

use App\Enums\ProductAvailability;
use App\Models\GalleryImage;
use App\Models\HeroSlide;
use App\Models\Post;
use App\Models\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function pages(): array
    {
        return [
            'home' => ['/'], 'about' => ['/about'], 'livestock' => ['/livestock'], 'poultry' => ['/poultry'],
            'palm' => ['/palm-oil'], 'sustainability' => ['/sustainability'], 'gallery' => ['/gallery'],
            'partners' => ['/partners'], 'contact' => ['/contact'], 'quote' => ['/quote'], 'products' => ['/products'],
            'news' => ['/news'], 'privacy' => ['/privacy-policy'], 'terms' => ['/terms'], 'cookies' => ['/cookie-policy'],
            'sitemap' => ['/sitemap'],
        ];
    }

    #[DataProvider('pages')]
    public function test_public_page_renders_with_content(string $uri): void
    {
        $this->seedContent();

        $this->get($uri)
            ->assertOk()
            ->assertSee('Request a Quote')
            ->assertSee('We value your privacy')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    #[DataProvider('pages')]
    public function test_public_page_renders_with_an_empty_database(string $uri): void
    {
        $this->get($uri)->assertOk();
    }

    public function test_homepage_shows_editable_slides_stats_products_and_posts(): void
    {
        $this->seedContent();

        $this->get('/')
            ->assertOk()
            ->assertSee('Building Sustainable Livestock and Palm Production')
            ->assertSee('Acres under agricultural management')
            ->assertSee('Table Eggs')
            ->assertSee('What consistent lay rates actually require')
            ->assertSee('aria-roledescription="carousel"', false);
    }

    public function test_navigation_marks_the_current_page(): void
    {
        $this->get('/livestock')->assertOk()->assertSee('aria-current="page"', false);
    }

    public function test_product_detail_shows_specs_and_quote_link(): void
    {
        $product = $this->makeProduct(attributes: ['specs' => [['label' => 'Grading', 'value' => 'Small / Medium / Large']]]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Small / Medium / Large')
            ->assertSee(route('quote', ['product' => $product->slug]), false);
    }

    public function test_out_of_season_product_offers_notify_me_instead_of_quote(): void
    {
        $product = $this->makeProduct('Processed Poultry', ProductAvailability::OutOfSeason);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Notify me about Processed Poultry')
            ->assertDontSee(route('quote', ['product' => $product->slug]), false);
    }

    public function test_unpublished_product_and_unpublished_posts_are_not_found(): void
    {
        $product = $this->makeProduct(attributes: ['is_published' => false]);
        $draft = Post::factory()->draft()->create();
        $scheduled = Post::factory()->scheduled()->create();

        $this->get(route('products.show', $product))->assertNotFound();
        $this->get(route('news.show', $draft))->assertNotFound();
        $this->get(route('news.show', $scheduled))->assertNotFound();
        $this->get('/products/does-not-exist')->assertNotFound()->assertSee('We can’t find that page');
    }

    public function test_article_body_is_sanitised(): void
    {
        $post = Post::factory()->create(['body' => '<p>Safe text</p><script>alert(1)</script><img src=x onerror="alert(2)">']);

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSee('Safe text')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('onerror', false);
    }

    public function test_gallery_respects_category_query(): void
    {
        GalleryImage::factory()->create(['title' => 'Cattle at the trough', 'category' => 'cattle']);
        GalleryImage::factory()->create(['title' => 'Hens in the run', 'category' => 'poultry']);

        $this->get('/gallery?category=poultry')
            ->assertOk()
            ->assertSee('Hens in the run')
            ->assertSee('aria-pressed="true"', false);
    }

    public function test_xml_sitemap_lists_published_products_and_posts(): void
    {
        $product = $this->makeProduct();
        $hidden = $this->makeProduct('Hidden Thing', attributes: ['is_published' => false]);
        $post = Post::factory()->create();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('products.show', $product), false)
            ->assertSee(route('news.show', $post), false)
            ->assertDontSee(route('products.show', $hidden), false);
    }

    public function test_health_endpoint_reports_ok(): void
    {
        $this->getJson('/health')->assertOk()->assertExactJson(['status' => 'ok', 'checks' => ['database' => 'ok', 'cache' => 'ok']]);
    }

    public function test_analytics_config_is_only_printed_when_configured(): void
    {
        $this->get('/')->assertDontSee('ht-analytics-config', false);

        config(['site.analytics' => ['provider' => 'plausible', 'id' => 'htintegratedfarms.com', 'src' => '']]);

        $this->get('/')->assertSee('ht-analytics-config', false);
    }

    private function seedContent(): void
    {
        HeroSlide::factory()->create(['title' => 'Building Sustainable Livestock and Palm Production']);
        Stat::factory()->create(['key' => 'home.acres', 'page' => 'home', 'value' => '240', 'label' => 'Acres under agricultural management']);
        $this->makeProduct(attributes: ['is_featured' => true]);
        Post::factory()->create(['title' => 'What consistent lay rates actually require']);
        GalleryImage::factory()->create(['is_featured' => true]);
    }
}
