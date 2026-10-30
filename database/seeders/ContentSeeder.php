<?php

namespace Database\Seeders;

use App\Enums\GalleryCategory;
use App\Enums\GallerySpan;
use App\Enums\ProductAvailability;
use App\Models\GalleryImage;
use App\Models\HeroSlide;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Stat;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\HasMedia;

/**
 * Initial website content from the design handoff. Idempotent: safe to re-run in any environment
 * (`php artisan db:seed --class=ContentSeeder --force`). Records are matched on natural keys and
 * images are attached only when missing, so edits made in the admin panel are kept.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        // Generate image conversions inline while seeding (no queue worker needed).
        config(['media-library.queue_conversions_by_default' => false]);

        $this->seedProducts();
        $this->seedGallery();
        $this->seedHeroSlides();
        $this->seedStats();
        $this->seedPosts();
    }

    private function seedProducts(): void
    {
        $categories = [];
        foreach ([['Livestock', 'Livestock', 'livestock'], ['Farm Produce', 'Produce', 'farm-produce'], ['Palm Products', 'Palm', 'palm-products']] as $sort => [$name, $short, $slug]) {
            $categories[$slug] = ProductCategory::updateOrCreate(['slug' => $slug], ['name' => $name, 'short_name' => $short, 'sort' => $sort]);
        }

        $onRequest = 'On request';
        $products = [
            [
                'slug' => 'table-eggs', 'name' => 'Table Eggs', 'category' => 'farm-produce', 'availability' => ProductAvailability::InStock, 'featured' => true, 'unit' => 'crates',
                'summary' => 'Graded and crated daily.',
                'featured_summary' => 'Graded and crated daily, available by the crate or by the pallet.',
                'description' => 'Collected, graded and crated the same day from our layer houses. Available by the crate for retail buyers and by the pallet for distributors and food businesses.',
                'specs' => ['Category' => 'Farm Produce · Eggs', 'Grading' => 'Small / Medium / Large', 'Packaging' => '30-egg crate · pallet', 'Minimum order' => $onRequest, 'Lead time' => $onRequest],
                'images' => ['eggs', 'gather', 'pullets', 'cage'],
            ],
            [
                'slug' => 'broiler-chickens', 'name' => 'Broiler Chickens', 'category' => 'livestock', 'availability' => ProductAvailability::InStock, 'featured' => true, 'unit' => 'birds',
                'summary' => 'Live or processed, batch sizes.',
                'featured_summary' => 'Live or processed, sold in commercial batch sizes.',
                'description' => 'Batch-reared to market weight under scheduled feeding, clean water and veterinary oversight. Supplied live or processed in commercial batch sizes, with dates agreed in advance.',
                'specs' => ['Category' => 'Livestock · Poultry', 'Supplied as' => 'Live or processed', 'Sold by' => 'Batch', 'Minimum order' => $onRequest, 'Lead time' => $onRequest],
                'images' => ['broilers'],
            ],
            [
                'slug' => 'point-of-lay-birds', 'name' => 'Point-of-Lay Birds', 'category' => 'livestock', 'availability' => ProductAvailability::InStock, 'featured' => false, 'unit' => 'birds',
                'summary' => 'Vaccinated, ready to lay.',
                'description' => 'Pullets raised to point of lay on a recorded vaccination programme, ready to move into production for egg farms and layer operations.',
                'specs' => ['Category' => 'Livestock · Poultry', 'Health' => 'Vaccinated on schedule', 'Sold by' => 'Bird', 'Minimum order' => $onRequest, 'Lead time' => $onRequest],
                'images' => ['pullets', 'hens'],
            ],
            [
                'slug' => 'market-weight-cattle', 'name' => 'Market-Weight Cattle', 'category' => 'livestock', 'availability' => ProductAvailability::OnRequest, 'featured' => true, 'unit' => 'head',
                'summary' => 'Sold per head or per lot.',
                'featured_summary' => 'Fattened stock with health records, sold per head or per lot.',
                'description' => 'Fattened stock with health records, raised under veterinary supervision and weight-tracked to target so supply can be scheduled accurately.',
                'specs' => ['Category' => 'Livestock · Cattle', 'Records' => 'Health and weight records', 'Sold by' => 'Head or lot', 'Minimum order' => $onRequest, 'Lead time' => $onRequest],
                'images' => ['cattle'],
            ],
            [
                'slug' => 'palm-oil', 'name' => 'Palm Oil', 'category' => 'palm-products', 'availability' => ProductAvailability::Seasonal, 'featured' => true, 'unit' => 'litres',
                'summary' => 'Bulk supply from our own mill.',
                'featured_summary' => 'Milled from our own fresh fruit bunches, supplied in bulk.',
                'description' => 'Milled from our own fresh fruit bunches promptly after harvest to protect quality, then clarified, stored and packaged for bulk or retail supply.',
                'specs' => ['Category' => 'Palm Products', 'Source' => 'Own plantation FFB', 'Packaging' => 'Bulk and retail formats', 'Minimum order' => $onRequest, 'Lead time' => $onRequest],
                'images' => ['oil'],
            ],
            [
                'slug' => 'fresh-fruit-bunches', 'name' => 'Fresh Fruit Bunches', 'category' => 'palm-products', 'availability' => ProductAvailability::Seasonal, 'featured' => false, 'unit' => 'tonnes',
                'summary' => 'Supplied to third-party mills.',
                'description' => 'Cut at bunch maturity on scheduled harvesting rounds and transported the same day, for mills that need consistent fruit supply.',
                'specs' => ['Category' => 'Palm Products', 'Harvest' => 'Cut at bunch maturity', 'Sold by' => 'Tonne', 'Minimum order' => $onRequest, 'Lead time' => $onRequest],
                'images' => ['ffb'],
            ],
            [
                'slug' => 'palm-kernels', 'name' => 'Palm Kernels', 'category' => 'palm-products', 'availability' => ProductAvailability::Seasonal, 'featured' => false, 'unit' => 'tonnes',
                'summary' => 'Recovered during processing.',
                'description' => 'Kernels recovered as a second product line during palm fruit processing, supplied to crushers and processors.',
                'specs' => ['Category' => 'Palm Products', 'Source' => 'Recovered at our mill', 'Sold by' => 'Tonne', 'Minimum order' => $onRequest, 'Lead time' => $onRequest],
                'images' => ['kernels'],
            ],
            [
                'slug' => 'processed-poultry', 'name' => 'Processed Poultry', 'category' => 'farm-produce', 'availability' => ProductAvailability::OutOfSeason, 'featured' => false, 'unit' => 'kg',
                'summary' => 'Dressed and chilled to order.',
                'description' => 'Broilers dressed and chilled to order for retailers, caterers and food businesses.',
                'specs' => ['Category' => 'Farm Produce · Poultry', 'Supplied as' => 'Dressed, chilled', 'Sold by' => 'Kilogram', 'Minimum order' => $onRequest, 'Lead time' => $onRequest],
                'images' => ['processed'],
            ],
        ];

        foreach ($products as $sort => $data) {
            $product = Product::updateOrCreate(['slug' => $data['slug']], [
                'product_category_id' => $categories[$data['category']]->id,
                'name' => $data['name'],
                'summary' => $data['summary'],
                'featured_summary' => $data['featured_summary'] ?? null,
                'description' => $data['description'],
                'specs' => collect($data['specs'])->map(fn (string $value, string $label) => ['label' => $label, 'value' => $value])->values()->all(),
                'availability' => $data['availability'],
                'unit' => $data['unit'],
                'is_featured' => $data['featured'],
                'is_published' => true,
                'sort' => $sort,
            ]);

            if (! $product->hasMedia('images')) {
                foreach ($data['images'] as $image) {
                    $this->attach($product, $image, 'images', "{$data['name']}");
                }
            }
        }
    }

    private function seedGallery(): void
    {
        $images = [
            ['farm', 'Mixed farm at morning light', GalleryCategory::Farm, GallerySpan::Large, false],
            ['pullets', 'Brown pullets in the henhouse', GalleryCategory::Poultry, null, true],
            ['ffb', 'Fresh oil palm fruit bunches', GalleryCategory::PalmPlantation, null, true],
            ['oil', 'Red palm oil, bottled and bulk', GalleryCategory::Processing, null, true],
            ['harvest', 'Oil palm harvest', GalleryCategory::PalmPlantation, null, false],
            ['team', 'Farm team reviewing site plans', GalleryCategory::FarmTeam, null, true],
            ['eggs', 'Fresh brown eggs in trays', GalleryCategory::Poultry, null, false],
            ['avenue', 'Plantation avenue', GalleryCategory::PalmPlantation, GallerySpan::Wide, false],
            ['cattle', 'Cattle care and feeding', GalleryCategory::Cattle, null, true],
            ['chicks', 'Day-old chicks', GalleryCategory::Poultry, GallerySpan::Tall, false],
            ['gather', 'Gathering eggs in the layer house', GalleryCategory::Poultry, null, false],
            ['hero-palm', 'Harvesting palm fruit bunches', GalleryCategory::PalmPlantation, GallerySpan::Wide, false],
            ['broilers', 'Broilers in housing', GalleryCategory::Poultry, null, false],
            ['seedlings', 'Workers tending young oil palms', GalleryCategory::PalmPlantation, null, false],
            ['hens', 'Hens on open range', GalleryCategory::Poultry, null, false],
            ['processed', 'Processed poultry display', GalleryCategory::Processing, null, false],
            ['grove', 'Palm grove', GalleryCategory::PalmPlantation, GallerySpan::Wide, false],
            ['whitehens', 'White layers in housing', GalleryCategory::Equipment, null, true],
            ['cage', 'Laying hens at the feed trough', GalleryCategory::Equipment, null, false],
            ['hero-poultry-field', 'Staff feeding free-range birds', GalleryCategory::Poultry, GallerySpan::Wide, false],
        ];

        foreach ($images as $sort => [$file, $title, $category, $span, $featured]) {
            $image = GalleryImage::updateOrCreate(['title' => $title], [
                'category' => $category,
                'span' => $span,
                'is_featured' => $featured,
                'is_published' => true,
                'sort' => ($sort + 1) * 10,
            ]);

            if (! $image->hasMedia('image')) {
                $this->attach($image, $file, 'image', $title);
            }
        }
    }

    private function seedHeroSlides(): void
    {
        $slides = [
            [
                'file' => 'hero-palm', 'position' => '50% 50%',
                'eyebrow' => 'Integrated Agriculture · Ogun State, Nigeria', 'mobile_eyebrow' => 'Ogun State, Nigeria',
                'title' => 'Building Sustainable Livestock and Palm Production',
                'body' => 'H & T Integrated Farms combines responsible livestock management, modern farming practices, palm cultivation and agricultural processing to deliver sustainable food production and long-term agricultural value.',
                'mobile_body' => 'Responsible livestock management, palm cultivation and agricultural processing.',
                'image_alt' => 'Workers harvesting oil palm fruit bunches',
            ],
            [
                'file' => 'hero-poultry-field', 'position' => '60% 50%',
                'eyebrow' => 'Poultry Production', 'mobile_eyebrow' => null,
                'title' => '50,000 birds raised under managed care',
                'body' => 'Scheduled feeding, clean water and veterinary oversight across our poultry houses and open runs.',
                'mobile_body' => 'Scheduled feeding, clean water and veterinary oversight.',
                'image_alt' => 'Farm staff feeding free-range chickens',
            ],
            [
                'file' => 'hero-poultry-house', 'position' => '70% 50%',
                'eyebrow' => 'Egg Production', 'mobile_eyebrow' => null,
                'title' => 'Consistent egg supply for commercial buyers',
                'body' => 'Daily collection, grading and packing for retailers, distributors and food businesses that need volume on schedule.',
                'mobile_body' => 'Daily collection, grading and packing for commercial buyers.',
                'image_alt' => 'Laying hens at a feed trough inside a poultry house',
            ],
        ];

        foreach ($slides as $sort => $data) {
            $slide = HeroSlide::updateOrCreate(['title' => $data['title']], [
                'eyebrow' => $data['eyebrow'],
                'mobile_eyebrow' => $data['mobile_eyebrow'],
                'body' => $data['body'],
                'mobile_body' => $data['mobile_body'],
                'image_alt' => $data['image_alt'],
                'image_position' => $data['position'],
                'primary_cta_label' => 'Explore Our Farm',
                'primary_cta_url' => '/about',
                'secondary_cta_label' => 'Our Operations',
                'secondary_cta_url' => '/livestock',
                'is_active' => true,
                'sort' => $sort,
            ]);

            if (! $slide->hasMedia('image')) {
                $this->attach($slide, $data['file'], 'image', $data['image_alt']);
            }
        }
    }

    private function seedStats(): void
    {
        // Figures supplied by the client for design purposes; editable in Admin → Stats.
        $stats = [
            ['home.acres', 'home', '240', 'Acres under agricultural management', 'Acres managed'],
            ['home.birds', 'home', '50,000', 'Birds in poultry production', 'Birds in production'],
            ['home.palm_trees', 'home', '15k+', 'Palm trees cultivated', 'Palm trees'],
            ['home.operations', 'home', '36', 'Active farm operations', 'Farm operations'],
            ['about.acres', 'about', '240', 'Acres under agricultural management', 'Acres managed'],
            ['about.birds', 'about', '50,000', 'Birds in poultry production', 'Birds'],
            ['about.animals', 'about', '15k+', 'Animals managed', 'Animals managed'],
            ['about.palm_trees', 'about', '15k+', 'Palm trees cultivated', 'Palm trees'],
            ['palm.ffb', 'palm', '10M+', 'FFB processed / season', null],
            ['palm.hectares', 'palm', '240+', 'Hectares in production', null],
            ['sustainability.waste', 'sustainability', '85%', 'Farm waste returned to land', null],
            ['sustainability.employment', 'sustainability', '120', 'People employed locally', null],
            ['sustainability.cover', 'sustainability', '64', 'Hectares under cover management', null],
            ['sustainability.vet_reviews', 'sustainability', '48', 'Veterinary reviews per year', null],
        ];

        foreach ($stats as $sort => [$key, $page, $value, $label, $short]) {
            Stat::firstOrCreate(['key' => $key], ['page' => $page, 'value' => $value, 'label' => $label, 'short_label' => $short, 'sort' => $sort]);
        }
    }

    private function seedPosts(): void
    {
        $posts = [
            [
                'slug' => 'what-consistent-lay-rates-actually-require',
                'title' => 'What consistent lay rates actually require',
                'category_label' => 'Poultry', 'topic' => 'Farm Management', 'minutes' => 4, 'date' => '2026-03-12', 'image' => 'gather',
                'excerpt' => 'Steady egg output is the result of routines kept every day: light, feed, water and calm handling.',
                'body' => <<<'HTML'
                    <p>Buyers who take eggs from us every week are not buying a single delivery; they are buying a rate. Keeping that rate steady across a flock's laying life comes down to a small number of routines, kept every day.</p>
                    <h2>Light and timing</h2>
                    <p>Layers respond to day length. We keep lighting programmes consistent in the houses so the flock is not pushed into and out of lay by changes it cannot predict.</p>
                    <h2>Feed and water</h2>
                    <p>Rations are set by age and production stage, and feed use is recorded per house. A drop in feed or water intake is usually the first sign that something is wrong, often days before it shows in the egg count.</p>
                    <h2>Calm, clean housing</h2>
                    <p>Stocking density, ventilation and litter condition all affect stress, and stressed birds lay less. Cleaning cycles and controlled access protect the flock from disease that would disrupt supply.</p>
                    <p>None of this is new. What makes the difference is recording it, reviewing it and acting on the numbers, which is how we can give buyers firm volumes.</p>
                    HTML,
            ],
            [
                'slug' => 'planning-harvest-rounds-around-bunch-maturity',
                'title' => 'Planning harvest rounds around bunch maturity',
                'category_label' => 'Palm', 'topic' => 'Plantation', 'minutes' => 6, 'date' => '2026-02-28', 'image' => 'harvest',
                'excerpt' => 'Harvesting on a fixed round, at the right ripeness, protects oil quality and keeps the mill supplied.',
                'body' => <<<'HTML'
                    <p>The quality of palm oil is decided long before the fruit reaches the mill. Bunches cut too early yield less oil; bunches left too long lose fruit and quality. Harvest rounds are how we balance the two.</p>
                    <h2>Fixed rounds</h2>
                    <p>The plantation is divided into blocks that are harvested on a regular cycle. Each round, cutters take only bunches that have reached maturity, judged by loose fruit and colour.</p>
                    <h2>Same-day transport</h2>
                    <p>Fresh fruit bunches are collected and moved to processing the same day. Prompt processing limits the build-up of free fatty acids, which is what buyers test for.</p>
                    <h2>Records that support supply</h2>
                    <p>Yields are recorded per block and per round. Over a season, those records let us forecast volumes and agree delivery dates with buyers of palm oil, kernels and fresh fruit bunches.</p>
                    HTML,
            ],
            [
                'slug' => 'biosecurity-routines-for-a-growing-herd',
                'title' => 'Biosecurity routines for a growing herd',
                'category_label' => 'Animal health', 'topic' => 'Livestock', 'minutes' => 5, 'date' => '2026-02-14', 'image' => 'cattle',
                'excerpt' => 'Disease prevention is a routine, not a response: access control, quarantine and scheduled veterinary review.',
                'body' => <<<'HTML'
                    <p>As a herd grows, so does the cost of a disease getting in. Our approach is to treat prevention as part of the daily routine rather than something we react to.</p>
                    <h2>Controlled access</h2>
                    <p>Visitors are logged, vehicles are disinfected and production zones are restricted. Most disease arrives on people, vehicles and equipment, so that is where control starts.</p>
                    <h2>Quarantine for new stock</h2>
                    <p>New animals are held apart and observed before joining the herd. It is a simple step that protects the animals already on the farm.</p>
                    <h2>Scheduled veterinary review</h2>
                    <p>Vaccination programmes are planned by species and age group, and veterinary inspections happen on a fixed cycle with records kept per unit. Problems found early are cheaper and kinder to deal with.</p>
                    HTML,
            ],
        ];

        foreach ($posts as $data) {
            $post = Post::updateOrCreate(['slug' => $data['slug']], [
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'body' => trim($data['body']),
                'category_label' => $data['category_label'],
                'topic' => $data['topic'],
                'reading_minutes' => $data['minutes'],
                'published_at' => $data['date'].' 09:00:00',
            ]);

            if (! $post->hasMedia('cover')) {
                $this->attach($post, $data['image'], 'cover', $data['title']);
            }
        }
    }

    private function attach(HasMedia $model, string $image, string $collection, string $name): void
    {
        $path = resource_path("images/{$image}.jpg");

        if (! is_file($path)) {
            $this->command?->warn("Missing seed image {$path}");

            return;
        }

        $model->addMedia($path)->preservingOriginal()->usingName($name)->toMediaCollection($collection);
    }
}
