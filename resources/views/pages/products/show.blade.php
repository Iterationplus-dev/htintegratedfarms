@php
    $images = $product->getMedia('images');
    $main = $images->first();
    $phone = $site->get('phone_primary');
@endphp
<x-layouts.app :title="$product->name" :description="trim($product->summary.' '.$product->description)" :og-image="$main?->getUrl('w1600')">
    <div class="hidden md:block">
        <div class="container-site py-[26px]">
            <x-breadcrumb :items="[['Products', route('products.index')], [$product->categoryName(), route('products.index', ['category' => $product->category?->slug])], [$product->name, null]]" />
        </div>
    </div>

    <section class="pb-[100px] md:pb-20">
        <div class="grid items-start gap-0 md:mx-auto md:max-w-[1440px] md:gap-10 md:px-10 xl:grid-cols-2 xl:gap-[72px] 2xl:px-[120px]">
            {{-- Gallery: main image + thumbnails; empty slots show the designed placeholder. --}}
            <div x-data="{ active: 0 }">
                <div class="h-[280px] overflow-hidden md:h-[440px] md:rounded-card">
                    @forelse ($images as $index => $image)
                        <x-picture :media="$image" :alt="$product->name.($index ? ' — view '.($index + 1) : '')" sizes="(min-width: 1280px) 600px, 100vw" :priority="$index === 0"
                                   class="h-full" x-show="active === {{ $index }}" :style="$index ? 'display: none' : null" />
                    @empty
                        <x-image-placeholder />
                    @endforelse
                </div>
                @if ($images->isNotEmpty())
                    <div class="mt-3 hidden grid-cols-4 gap-3 md:grid" role="group" aria-label="Product images">
                        @foreach ($images->take(4) as $index => $image)
                            <button type="button" @click="active = {{ $index }}" class="h-[90px] cursor-pointer overflow-hidden rounded-ui ring-g500 ring-offset-2" :class="active === {{ $index }} && 'ring-2'" aria-label="Show image {{ $index + 1 }}">
                                <x-picture :media="$image" alt="" sizes="150px" class="h-full" />
                            </button>
                        @endforeach
                        @for ($i = $images->take(4)->count(); $i < 4; $i++)
                            <div class="h-[90px] overflow-hidden rounded-ui"><x-image-placeholder label="Coming soon" /></div>
                        @endfor
                    </div>
                @endif
            </div>

            <div class="px-5 pt-[22px] md:px-0 md:pt-0">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-semibold tracking-[0.12em] text-ink-3 uppercase">{{ $product->categoryName() }}</span>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold md:px-[11px] md:py-[5px] {{ $product->availability->badgeClasses() }}">{{ $product->availability->getLabel() }}</span>
                </div>
                <h1 class="mt-3 text-[31px] md:mt-3.5 md:text-[42px]">{{ $product->name }}</h1>
                @if ($product->description)
                    <p class="mt-3 text-[15px] leading-[1.7] text-ink-2 md:mt-4 md:text-[17px] md:leading-[1.75]">{{ $product->description }}</p>
                @endif

                @if (! empty($product->specs))
                    <dl class="mt-[22px] rounded-card border border-line md:mt-[30px]">
                        @foreach ($product->specs as $spec)
                            <div class="flex justify-between gap-6 border-b border-line px-[18px] py-3.5 text-sm last:border-b-0 md:px-[22px] md:py-4 md:text-[15px]">
                                <dt class="text-ink-3">{{ $spec['label'] }}</dt>
                                <dd class="text-right font-semibold">{{ $spec['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($product->isQuotable())
                    <div class="mt-[26px] hidden gap-3 md:flex">
                        <a href="{{ route('quote', ['product' => $product->slug]) }}" class="btn btn-primary btn-lg flex-1">Request a Quote</a>
                        <a href="{{ route('contact', ['product' => $product->slug]) }}" class="btn btn-navy-outline btn-lg">Order Inquiry</a>
                    </div>
                @else
                    <div id="notify" class="mt-[26px] rounded-card border border-line p-6">
                        <livewire:stock-alert-form :product="$product" />
                    </div>
                @endif
                <p class="mt-[18px] rounded-ui bg-g100 px-[18px] py-3.5 text-sm text-ink-2">
                    Typical response within one working day · <a href="{{ $site->telHref($phone) }}" class="font-semibold">{{ $phone }}</a>
                </p>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="border-t border-line bg-bg py-[34px] md:py-[70px]" aria-labelledby="related">
            <div class="container-site">
                <h2 id="related" class="mb-5 text-2xl md:mb-7 md:text-[28px]">Related products</h2>
                <div class="grid grid-cols-2 gap-3.5 md:gap-6 xl:grid-cols-4">
                    @foreach ($related as $item)
                        <a href="{{ route('products.show', $item) }}" class="card group block text-ink hover:text-g700">
                            <x-picture :media="$item->cover()" :alt="$item->name" sizes="(min-width: 1280px) 290px, 50vw" class="h-[120px] md:h-[150px]" img-class="h-full w-full object-cover transition-transform duration-[400ms] group-hover:scale-[1.04]" />
                            <h3 class="p-3.5 text-base md:p-[18px] md:text-[17px]">{{ $item->name }}</h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Mobile sticky action bar (handoff §06 mobile). --}}
    @if ($product->isQuotable())
        <div class="fixed inset-x-0 bottom-0 z-30 flex gap-2.5 border-t border-line bg-surface px-5 py-3.5 md:hidden">
            <a href="{{ route('quote', ['product' => $product->slug]) }}" class="btn btn-primary btn-lg flex-1">Request a Quote</a>
            <a href="{{ $site->telHref($phone) }}" class="btn btn-navy-outline btn-lg px-4"><x-ph name="phone" class="size-4" /> Call</a>
        </div>
    @endif
</x-layouts.app>
