<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private const STATIC_ROUTES = [
        'home', 'about', 'livestock', 'poultry', 'palm', 'products.index', 'gallery', 'partners',
        'contact', 'quote', 'sustainability', 'news.index', 'privacy', 'terms', 'cookies', 'sitemap',
    ];

    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function (): string {
            $urls = array_map(fn (string $name): array => ['loc' => route($name), 'lastmod' => null], self::STATIC_ROUTES);

            foreach (Product::query()->published()->get(['slug', 'updated_at']) as $product) {
                $urls[] = ['loc' => route('products.show', $product), 'lastmod' => $product->updated_at?->toAtomString()];
            }
            foreach (Post::query()->published()->get(['slug', 'updated_at']) as $post) {
                $urls[] = ['loc' => route('news.show', $post), 'lastmod' => $post->updated_at?->toAtomString()];
            }

            return view('sitemap-xml', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
