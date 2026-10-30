<?php

namespace App\Http\Controllers;

use App\Enums\GalleryCategory;
use App\Enums\PartnershipType;
use App\Models\GalleryImage;
use App\Models\HeroSlide;
use App\Models\Post;
use App\Models\Product;
use App\Models\Stat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** Content pages. Copy lives in the Blade views; editable data (stats, slides, products…) comes from the DB. */
class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'slides' => HeroSlide::query()->active()->with('media')->get(),
            'stats' => Stat::forPage('home'),
            'featuredProducts' => Product::query()->published()->where('is_featured', true)->with(['category', 'media'])->ordered()->limit(4)->get(),
            'galleryTeaser' => GalleryImage::query()->published()->where('is_featured', true)->with('media')->ordered()->limit(6)->get(),
            'posts' => Post::query()->published()->with('media')->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function about(): View
    {
        return view('pages.about', ['stats' => Stat::forPage('about')]);
    }

    public function livestock(): View
    {
        return view('pages.livestock');
    }

    public function poultry(): View
    {
        return view('pages.poultry', [
            'products' => Product::query()->published()
                ->whereIn('slug', ['table-eggs', 'broiler-chickens', 'point-of-lay-birds', 'processed-poultry'])
                ->with(['category', 'media'])->ordered()->get(),
        ]);
    }

    public function palm(): View
    {
        return view('pages.palm', ['stats' => Stat::forPage('palm')]);
    }

    public function sustainability(): View
    {
        return view('pages.sustainability', ['stats' => Stat::forPage('sustainability')]);
    }

    public function gallery(Request $request): View
    {
        $category = GalleryCategory::tryFrom((string) $request->query('category'));

        return view('pages.gallery', [
            'images' => GalleryImage::query()->published()->with('media')->ordered()->get(),
            'categories' => GalleryCategory::filterable(),
            'activeCategory' => $category && in_array($category, GalleryCategory::filterable(), true) ? $category->value : 'all',
        ]);
    }

    public function partners(): View
    {
        return view('pages.partners', ['types' => PartnershipType::showcased()]);
    }

    public function contact(Request $request): View
    {
        return view('pages.contact', [
            'interest' => $request->string('interest')->limit(40, '')->toString() ?: null,
            'product' => $request->string('product')->limit(140, '')->toString() ?: null,
        ]);
    }

    public function quote(Request $request): View
    {
        return view('pages.quote', [
            'product' => $request->string('product')->limit(140, '')->toString() ?: null,
        ]);
    }

    public function privacy(): View
    {
        return view('pages.legal.privacy');
    }

    public function terms(): View
    {
        return view('pages.legal.terms');
    }

    public function cookies(): View
    {
        return view('pages.legal.cookies');
    }

    public function sitemap(): View
    {
        return view('pages.sitemap', [
            'products' => Product::query()->published()->ordered()->get(['name', 'slug']),
            'posts' => Post::query()->published()->latest('published_at')->get(['title', 'slug']),
        ]);
    }
}
