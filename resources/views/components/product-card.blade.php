@props(['product', 'featured' => false])
{{--
    Product card (handoff §01 Featured / §05 catalogue). One responsive card:
    compact on mobile (status text + "Quote"), full on md+ (badge, category, summary, "Request quote").
--}}
@php
    $summary = $featured && $product->featured_summary ? $product->featured_summary : $product->summary;
    $url = route('products.show', $product);
@endphp
<article {{ $attributes->class('card flex flex-col') }}>
    <a href="{{ $url }}" class="relative block h-[120px] overflow-hidden md:h-[180px] {{ $featured ? 'xl:h-[190px]' : '' }}" tabindex="-1" aria-hidden="true">
        <x-picture :media="$product->cover()" :alt="$product->name" sizes="(min-width: 1280px) 290px, (min-width: 768px) 45vw, 50vw" class="h-full" />
        <span class="absolute top-3 left-3 hidden rounded-full px-[11px] py-[5px] text-[11px] font-semibold md:inline-flex {{ $product->availability->badgeClasses() }}">{{ $product->availability->getLabel() }}</span>
    </a>
    <div class="flex flex-1 flex-col p-3.5 md:p-5 {{ $featured ? 'xl:p-[22px]' : '' }}">
        <p class="hidden text-[11px] font-medium tracking-[0.12em] text-ink-3 uppercase md:block">{{ $product->categoryName() }}</p>
        <h3 class="text-base md:mt-2 md:text-lg {{ $featured ? 'xl:text-[19px]' : '' }}">
            <a href="{{ $url }}" class="text-ink hover:text-g700">{{ $product->name }}</a>
        </h3>
        <p class="mt-1.5 text-xs font-medium md:hidden {{ $product->availability->textClass() }}">{{ $product->availability->getLabel() }}</p>
        <p class="mt-2 hidden text-sm leading-[1.6] text-ink-2 md:block">{{ $summary }}</p>
        <div class="mt-auto pt-2.5 md:pt-3.5 {{ $featured ? 'xl:pt-4' : '' }}">
            @if ($product->isQuotable())
                <a href="{{ route('quote', ['product' => $product->slug]) }}" class="btn btn-outline min-h-11 w-full px-2.5 text-[13px] md:text-sm">
                    <span class="md:hidden">Quote</span><span class="hidden md:inline">Request quote</span>
                    <span class="sr-only">for {{ $product->name }}</span>
                </a>
            @else
                <div x-data="{ open: false }">
                    <button type="button" @click="open = true" class="btn w-full border-line bg-transparent px-2.5 text-[13px] text-ink-3 hover:border-g500 hover:text-g700 md:text-sm">
                        <x-ph name="bell" class="size-4" /> Notify me <span class="sr-only">when {{ $product->name }} is available</span>
                    </button>
                    {{-- Fixed-position dialog rendered in place (no teleport) so the nested Livewire component initialises normally. --}}
                    <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-end justify-center p-3 md:items-center" role="dialog" aria-modal="true" aria-label="Notify me about {{ $product->name }}"
                         x-trap.inert.noscroll="open" @keydown.escape.window="open = false">
                        <div class="absolute inset-0 bg-[rgba(9,20,13,.6)]" @click="open = false"></div>
                        <div class="relative w-full max-w-[460px] rounded-card bg-surface p-6 shadow-lg md:p-7">
                            <button type="button" @click="open = false" class="absolute top-3 right-3 flex size-11 cursor-pointer items-center justify-center rounded-ui text-ink-2 hover:bg-bg">
                                <span class="sr-only">Close</span><x-ph name="x" class="size-5" />
                            </button>
                            <livewire:stock-alert-form :product="$product" :key="'stock-alert-'.$product->id" />
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</article>
