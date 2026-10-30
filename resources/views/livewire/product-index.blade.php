@php $products = $this->products; @endphp
<div class="bg-bg">
    {{-- Filter bar --}}
    <div class="md:border-t md:border-line">
        <div class="container-site flex flex-col gap-4 pb-[18px] md:flex-row md:flex-wrap md:items-center md:gap-3 md:py-[22px]">
            <div class="order-2 -mx-5 overflow-x-auto px-5 md:order-1 md:mx-0 md:overflow-visible md:px-0">
                <div class="flex gap-2 md:gap-3" role="group" aria-label="Filter by category">
                    <button type="button" wire:click="setCategory('')" class="chip" aria-pressed="{{ $category === '' ? 'true' : 'false' }}">
                        <span class="md:hidden">All</span><span class="hidden md:inline">All products</span>
                    </button>
                    @foreach ($this->categories as $cat)
                        <button type="button" wire:click="setCategory(@js($cat->slug))" wire:key="cat-{{ $cat->id }}" class="chip" aria-pressed="{{ $category === $cat->slug ? 'true' : 'false' }}">
                            <span class="md:hidden">{{ $cat->chipLabel(true) }}</span><span class="hidden md:inline">{{ $cat->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="hidden flex-1 md:order-2 md:block"></div>
            <div class="order-1 md:order-3 md:w-[260px]">
                <label for="product-search" class="sr-only">Search products</label>
                <div class="relative">
                    <x-ph name="magnifying-glass" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-ink-3" />
                    <input id="product-search" type="search" wire:model.live.debounce.350ms="search" placeholder="Search products…" maxlength="80" autocomplete="off"
                           class="min-h-[46px] w-full rounded-ui border border-line bg-surface py-[11px] pr-4 pl-10 text-sm text-ink placeholder:text-ink-3 focus:border-g600 focus:shadow-[0_0_0_3px_var(--color-g100)] focus:outline-none">
                </div>
            </div>
            <div class="order-3 md:order-4">
                <label for="product-availability" class="sr-only">Availability</label>
                <div class="relative">
                    <select id="product-availability" wire:model.live="availability"
                            class="min-h-[46px] w-full cursor-pointer appearance-none rounded-ui border border-line bg-surface py-[11px] pr-10 pl-4 text-sm font-medium text-ink-2 focus:border-g600 focus:outline-none md:w-auto">
                        <option value="">Availability</option>
                        @foreach ($availabilities as $option)
                            <option value="{{ $option->value }}">{{ $option->getLabel() }}</option>
                        @endforeach
                    </select>
                    <x-ph name="caret-down" class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-ink-3" />
                </div>
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="container-site pt-2 pb-[34px] md:pt-11 md:pb-[90px]">
        <p class="sr-only" aria-live="polite">{{ $products->total() }} {{ Str::plural('product', $products->total()) }} found</p>

        <div class="relative" wire:loading.class="opacity-60 pointer-events-none" wire:target="setCategory,search,availability,clearFilters,gotoPage,nextPage,previousPage">
            @if ($products->isEmpty())
                <div class="rounded-card border border-dashed border-input bg-surface px-6 py-14 text-center">
                    <x-ph name="magnifying-glass" class="mx-auto size-8 text-ink-3" />
                    <h2 class="mt-4 text-xl">No products match your filters</h2>
                    <p class="mx-auto mt-2 max-w-[420px] text-sm leading-[1.6] text-ink-2">Try a different category or search term, or tell us what you need and we will check availability.</p>
                    <div class="mt-6 flex flex-col justify-center gap-3 md:flex-row">
                        @if ($this->hasFilters())
                            <button type="button" wire:click="clearFilters" class="btn btn-outline">Clear filters</button>
                        @endif
                        <a href="{{ route('contact', ['interest' => 'commercial_supply']) }}" class="btn btn-primary">Ask our sales team</a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3 md:gap-6 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" wire:key="product-{{ $product->id }}" />
                    @endforeach
                </div>
                @if ($products->hasPages())
                    <div class="mt-10">{{ $products->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
