<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('pages.products.index');
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published, 404);

        $product->loadMissing(['category', 'media']);

        $related = Product::query()
            ->published()
            ->whereKeyNot($product->getKey())
            ->with(['category', 'media'])
            ->orderByRaw('case when product_category_id = ? then 0 else 1 end', [$product->product_category_id])
            ->ordered()
            ->limit(4)
            ->get();

        return view('pages.products.show', ['product' => $product, 'related' => $related]);
    }
}
