<x-layouts.app title="Sitemap" description="All pages on the H & T Integrated Farms website.">
    <section class="bg-bg">
        <div class="container-site pt-[26px] pb-8 md:pt-[52px] md:pb-12">
            <h1 class="text-[31px] md:text-[46px]">Sitemap</h1>
        </div>
    </section>
    <div class="container-site grid gap-10 py-10 md:grid-cols-3 md:py-16">
        @foreach ([
            'The farm' => [['Home', route('home')], ['About Us', route('about')], ['Livestock', route('livestock')], ['Poultry', route('poultry')], ['Palm Production', route('palm')], ['Sustainability', route('sustainability')], ['Gallery', route('gallery')]],
            'Work with us' => [['Products', route('products.index')], ['Request a Quote', route('quote')], ['Partnerships', route('partners')], ['Contact', route('contact')], ['News', route('news.index')]],
            'Legal' => [['Privacy Policy', route('privacy')], ['Terms & Conditions', route('terms')], ['Cookie Policy', route('cookies')]],
        ] as $heading => $links)
            <nav aria-label="{{ $heading }}">
                <h2 class="text-xl">{{ $heading }}</h2>
                <ul class="mt-3 space-y-2 text-[15px]">
                    @foreach ($links as [$label, $url])
                        <li><a href="{{ $url }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endforeach
        @if ($products->isNotEmpty())
            <nav aria-label="Products">
                <h2 class="text-xl">Products</h2>
                <ul class="mt-3 space-y-2 text-[15px]">
                    @foreach ($products as $product)
                        <li><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endif
        @if ($posts->isNotEmpty())
            <nav aria-label="Farm updates" class="md:col-span-2">
                <h2 class="text-xl">Farm updates</h2>
                <ul class="mt-3 space-y-2 text-[15px]">
                    @foreach ($posts as $post)
                        <li><a href="{{ route('news.show', $post) }}">{{ $post->title }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endif
    </div>
</x-layouts.app>
