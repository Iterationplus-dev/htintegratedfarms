@php
    $items = $images->filter(fn ($image) => $image->photo())->map(function ($image) {
        $media = $image->photo();

        return [
            'id' => $image->id,
            'title' => $image->title,
            'category' => $image->category->value,
            'categoryLabel' => $image->category->getLabel(),
            'src' => $media->hasGeneratedConversion('fallback') ? $media->getUrl('fallback') : $media->getUrl(),
            'srcset' => collect([800, 1600])->filter(fn ($w) => $media->hasGeneratedConversion("w{$w}"))->map(fn ($w) => $media->getUrl("w{$w}")." {$w}w")->implode(', '),
        ];
    })->values();
@endphp
<x-layouts.app title="Farm Gallery" description="Photographs from the H & T livestock units, the oil palm plantation and the processing floor.">
    <div x-data="gallery(@js($items), @js($activeCategory))" @keydown.window="onKeydown($event)">
        <section>
            <div class="container-site pt-[26px] pb-4 md:pt-[52px] md:pb-[26px]">
                <h1 class="text-[31px] md:text-[46px]">Farm Gallery</h1>
                <p class="mt-3 hidden max-w-[600px] text-[17px] leading-[1.7] text-ink-2 md:block">Photographs from the livestock units, the plantation and the processing floor.</p>
            </div>
            <div class="container-site pb-4 md:pb-7">
                <div class="-mx-5 overflow-x-auto px-5 md:mx-0 md:px-0">
                    <div class="flex gap-2 md:gap-2.5" role="group" aria-label="Filter photos by category">
                        <a href="{{ route('gallery') }}" @click.prevent="setCategory('all')" class="chip" :aria-pressed="(category === 'all').toString()" aria-pressed="{{ $activeCategory === 'all' ? 'true' : 'false' }}">All</a>
                        @foreach ($categories as $cat)
                            <a href="{{ route('gallery', ['category' => $cat->value]) }}" @click.prevent="setCategory(@js($cat->value))" class="chip"
                               :aria-pressed="(category === @js($cat->value)).toString()" aria-pressed="{{ $activeCategory === $cat->value ? 'true' : 'false' }}">{{ $cat->getLabel() }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="container-site pb-[34px] md:pb-[90px]" aria-label="Photos">
            <p class="sr-only" aria-live="polite" x-text="visible.length + ' photos shown'"></p>
            @if ($images->isEmpty())
                <p class="rounded-card border border-dashed border-input px-6 py-14 text-center text-ink-2">Photos will be added soon.</p>
            @else
                <ul class="grid grid-flow-dense auto-rows-[130px] grid-cols-2 gap-2.5 md:auto-rows-[210px] md:grid-cols-4 md:gap-4">
                    @foreach ($images as $image)
                        <li @class(['min-w-0', $image->span?->gridClasses()])
                            @if ($activeCategory !== 'all' && $image->category->value !== $activeCategory) style="display: none" @endif
                            x-show="matches(@js($image->category->value))">
                            <a href="{{ $image->photo()?->getUrl() }}" @click.prevent="open({{ $image->id }})"
                               class="group block h-full cursor-zoom-in overflow-hidden rounded-ui" aria-label="Open photo: {{ $image->title }}">
                                <x-picture :media="$image->photo()" :alt="$image->title" :sizes="$image->span ? '(min-width: 768px) 50vw, 100vw' : '(min-width: 1280px) 290px, (min-width: 768px) 25vw, 50vw'"
                                           class="h-full" img-class="h-full w-full object-cover transition-transform duration-[400ms] group-hover:scale-[1.04]" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Lightbox (handoff §08) --}}
        <div x-cloak x-show="current" x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(9,20,13,.86)] p-3 md:p-[70px]"
             role="dialog" aria-modal="true" aria-labelledby="lightbox-title"
             x-trap.inert.noscroll="current !== null" @click.self="close()"
             @touchstart.passive="onTouchStart($event)" @touchend.passive="onTouchEnd($event)">
            <figure class="w-full max-w-[900px] overflow-hidden rounded-card bg-lightbox shadow-lg">
                <template x-if="current">
                    <img :src="current.src" :srcset="current.srcset" sizes="(min-width: 980px) 900px, 100vw" :alt="current.title"
                         class="block h-[56vh] max-h-[500px] w-full object-cover md:h-[500px]">
                </template>
                <figcaption class="flex flex-col gap-4 px-5 py-4 md:flex-row md:items-center md:justify-between md:px-[26px] md:py-5">
                    <div>
                        <p id="lightbox-title" class="text-[15px] font-semibold text-white" x-text="current?.title"></p>
                        <p class="mt-1 text-[13px] text-lightbox-muted" x-text="meta"></p>
                    </div>
                    <div class="flex gap-2.5">
                        <button type="button" @click="prev()" class="flex size-11 cursor-pointer items-center justify-center rounded-ui border border-white/25 text-white hover:bg-white/10" aria-label="Previous photo"><x-ph name="arrow-left" class="size-5" /></button>
                        <button type="button" @click="next()" class="flex size-11 cursor-pointer items-center justify-center rounded-ui border border-white/25 text-white hover:bg-white/10" aria-label="Next photo"><x-ph name="arrow-right" class="size-5" /></button>
                        <button type="button" @click="close()" class="flex size-11 cursor-pointer items-center justify-center rounded-ui border border-white/25 text-white hover:bg-white/10" aria-label="Close"><x-ph name="x" class="size-5" /></button>
                    </div>
                </figcaption>
            </figure>
        </div>
    </div>
</x-layouts.app>
