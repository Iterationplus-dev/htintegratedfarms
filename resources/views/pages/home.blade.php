@php use App\Support\SharedContent; @endphp
<x-layouts.app>
    {{-- 1 · Hero slider (handoff §01.1) --}}
    @if ($slides->isNotEmpty())
        <section x-data="heroSlider({{ $slides->count() }})"
                 @mouseenter="setHover(true)" @mouseleave="setHover(false)" @focusin="setFocus(true)" @focusout="setFocus(false)"
                 aria-roledescription="carousel" aria-label="Highlights"
                 class="relative h-[520px] overflow-hidden bg-g900 md:h-[580px] xl:h-[620px]">
            @foreach ($slides as $index => $slide)
                <div role="group" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ $slides->count() }}"
                     class="absolute inset-0 transition-opacity duration-[900ms] ease-[ease]"
                     style="opacity: {{ $loop->first ? 1 : 0 }}; pointer-events: {{ $loop->first ? 'auto' : 'none' }}"
                     :style="{ opacity: isActive({{ $index }}) ? 1 : 0, pointerEvents: isActive({{ $index }}) ? 'auto' : 'none' }"
                     @if (! $loop->first) inert aria-hidden="true" @endif
                     x-effect="$el.inert = !isActive({{ $index }}); $el.setAttribute('aria-hidden', (!isActive({{ $index }})).toString())">
                    <div class="absolute inset-0 overflow-hidden xl:left-auto xl:w-[64%]">
                        <x-picture :media="$slide->photo()" :alt="$slide->image_alt" :position="$slide->image_position"
                                   sizes="(min-width: 1280px) 64vw, 100vw" :priority="$loop->first"
                                   class="h-full transition-transform duration-[6500ms] ease-out"
                                   x-bind:style="'transform: scale(' + (isActive({{ $index }}) && !reducedMotion ? 1.06 : 1) + ')'" />
                    </div>
                    <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(9,32,18,.2)_0%,rgba(9,32,18,.75)_45%,rgba(9,32,18,.96)_100%)] xl:bg-[linear-gradient(90deg,#0E3B21_0%,#0E3B21_36%,rgba(9,32,18,.7)_52%,rgba(9,32,18,.25)_75%,rgba(9,32,18,.1)_100%)]"></div>
                    <div class="absolute inset-0 flex flex-col justify-end">
                        <div class="container-site pt-6 pb-[76px] md:pb-24 xl:flex xl:h-full xl:flex-col xl:justify-center xl:pb-10">
                            <div class="xl:max-w-[700px]">
                                <p class="mb-3 text-[11px] font-semibold tracking-[0.16em] text-on-dark-accent uppercase xl:mb-5 xl:text-xs xl:tracking-[0.18em]">
                                    <span class="xl:hidden">{{ $slide->mobile_eyebrow ?: $slide->eyebrow }}</span>
                                    <span class="hidden xl:inline">{{ $slide->eyebrow }}</span>
                                </p>
                                @if ($loop->first)
                                    <h1 class="text-[30px] leading-[1.1] text-white md:text-[42px] xl:max-w-[640px] xl:text-[56px] xl:leading-[1.06]">{{ $slide->title }}</h1>
                                @else
                                    <h2 class="text-[30px] leading-[1.1] text-white md:text-[42px] xl:max-w-[640px] xl:text-[56px] xl:leading-[1.06]">{{ $slide->title }}</h2>
                                @endif
                                <p class="mt-3 text-[15px] leading-[1.65] text-on-dark md:max-w-[560px] xl:mt-[22px] xl:text-lg xl:leading-[1.7]">
                                    <span class="xl:hidden">{{ $slide->mobile_body ?: $slide->body }}</span>
                                    <span class="hidden xl:inline">{{ $slide->body }}</span>
                                </p>
                                <div class="mt-5 flex gap-3.5 xl:mt-[34px]">
                                    @if ($slide->primary_cta_label && $slide->primary_cta_url)
                                        <a href="{{ $slide->primary_cta_url }}" class="btn btn-light btn-lg w-full md:w-auto">{{ $slide->primary_cta_label }}</a>
                                    @endif
                                    @if ($slide->secondary_cta_label && $slide->secondary_cta_url)
                                        <a href="{{ $slide->secondary_cta_url }}" class="btn btn-ghost-light btn-lg hidden md:inline-flex">{{ $slide->secondary_cta_label }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($slides->count() > 1)
                <div class="absolute inset-x-0 bottom-5 xl:bottom-10">
                    <div class="container-site flex items-center gap-3.5 xl:gap-5">
                        <p class="hidden text-sm font-semibold tracking-[0.06em] text-white xl:block" aria-hidden="true">
                            <span x-text="counter">01</span> <span class="text-white/50">/ {{ str_pad((string) $slides->count(), 2, '0', STR_PAD_LEFT) }}</span>
                        </p>
                        <div class="flex gap-1.5 xl:gap-2">
                            @foreach ($slides as $index => $slide)
                                <button type="button" @click="go({{ $index }})" aria-label="Show slide {{ $index + 1 }}"
                                        :aria-current="isActive({{ $index }}) ? 'true' : 'false'"
                                        class="flex h-6 cursor-pointer items-center transition-[width] duration-[400ms]"
                                        style="width: {{ $loop->first ? 56 : 28 }}px" :style="{ width: isActive({{ $index }}) ? '56px' : '28px' }">
                                    <span class="block h-[3px] w-full rounded-sm transition-colors duration-[400ms]"
                                          style="background: {{ $loop->first ? '#fff' : 'rgba(255,255,255,.3)' }}" :style="{ background: isActive({{ $index }}) ? '#FFFFFF' : 'rgba(255,255,255,.3)' }"></span>
                                </button>
                            @endforeach
                        </div>
                        <div class="ml-auto flex gap-2 xl:gap-2.5">
                            <button type="button" @click="prev()" class="flex size-11 cursor-pointer items-center justify-center rounded-ui border border-white/40 text-white hover:bg-white/12" aria-label="Previous slide">
                                <x-ph name="arrow-left" class="size-5" />
                            </button>
                            <button type="button" @click="next()" class="flex size-11 cursor-pointer items-center justify-center rounded-ui border border-white/40 text-white hover:bg-white/12" aria-label="Next slide">
                                <x-ph name="arrow-right" class="size-5" />
                            </button>
                        </div>
                    </div>
                </div>
                <p class="sr-only" aria-live="polite" aria-atomic="true" x-text="announcement"></p>
            @endif
        </section>
    @else
        <section class="bg-g900 py-24">
            <div class="container-site">
                <h1 class="max-w-[640px] text-[30px] text-white md:text-[56px]">Building Sustainable Livestock and Palm Production</h1>
            </div>
        </section>
    @endif

    {{-- 2 · Stats strip --}}
    @if ($stats->isNotEmpty())
        <section aria-label="Farm in numbers" class="border-t border-white/8 bg-stats-dark">
            <dl class="container-site grid grid-cols-2 gap-5 py-6 md:grid-cols-4 md:gap-6 md:py-11">
                @foreach ($stats as $stat)
                    <div class="flex flex-col-reverse">
                        <dt class="mt-1 text-xs font-medium text-on-dark-accent md:mt-1.5 md:text-[13px] md:tracking-[0.06em]">
                            <span class="md:hidden">{{ $stat->compactLabel() }}</span><span class="hidden md:inline">{{ $stat->label }}</span>
                        </dt>
                        <dd class="text-[30px] font-extrabold text-white md:text-[36px] md:tracking-[-0.03em] xl:text-[44px]">{{ $stat->value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    {{-- 3 · Who we are --}}
    <section class="bg-surface py-10 md:py-16 xl:py-24" aria-labelledby="who-we-are">
        <div class="container-site grid items-center gap-8 xl:grid-cols-2 xl:gap-[72px]">
            <div>
                <p class="eyebrow mb-3 md:mb-[18px]" id="who-we-are">Who We Are</p>
                <h2 class="mb-4 text-[27px] md:text-[34px] xl:hidden">An integrated agricultural enterprise, managed professionally</h2>
                <p class="max-w-[520px] text-[15px] leading-[1.75] text-ink-2 md:text-base">We operate across two connected divisions — livestock and oil palm — supported by structured farm management, veterinary oversight and a production process built for consistent commercial supply.</p>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:mt-[34px]">
                    <x-feature-note title="Structured management">Recorded stock, scheduled feeding, monitored health.</x-feature-note>
                    <x-feature-note title="Commercial reliability" accent="sky">Supply agreements for buyers who need volume on schedule.</x-feature-note>
                </div>
                <a href="{{ route('about') }}" class="btn btn-outline btn-lg mt-8 xl:mt-[34px]">About H &amp; T</a>
            </div>
            <div class="flex flex-col gap-7">
                <h2 class="hidden max-w-[520px] text-[40px] xl:block">An integrated agricultural enterprise, managed professionally</h2>
                <div class="grid grid-cols-2 grid-rows-[150px_150px] gap-3 md:grid-rows-[200px_200px] md:gap-4">
                    <x-static-picture src="cattle" alt="Farm manager with the cattle herd" sizes="(min-width: 1280px) 290px, 50vw" class="row-span-2 overflow-hidden rounded-ui" />
                    <x-static-picture src="pullets" alt="Poultry house interior" sizes="(min-width: 1280px) 290px, 50vw" class="overflow-hidden rounded-ui" />
                    <x-static-picture src="seedlings" alt="Palm seedlings in the nursery" sizes="(min-width: 1280px) 290px, 50vw" class="overflow-hidden rounded-ui" />
                </div>
            </div>
        </div>
    </section>

    {{-- 4 · Division A — Livestock --}}
    <section class="border-t border-line bg-surface py-10 md:bg-bg md:py-16 xl:py-24" aria-labelledby="division-a">
        <div class="container-site">
            <div class="mb-6 flex flex-col gap-3 md:mb-11 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="eyebrow mb-3 md:mb-[18px]">Division A</p>
                    <h2 id="division-a" class="text-[27px] md:text-[34px] xl:text-[40px]">Livestock Production &amp; Management</h2>
                </div>
                <a href="{{ route('livestock') }}" class="link-arrow hidden md:inline-flex">View livestock operations <span aria-hidden="true">→</span></a>
            </div>
            <div class="grid gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
                @foreach ([
                    ['cattle', 'Cattle herd', 'Cattle Production', 'Breeding, herd management, fattening and health monitoring under veterinary supervision.', ['Breeding', 'Nutrition', 'Biosecurity'], false],
                    ['broilers', 'Broilers in housing', 'Poultry Production', 'Broiler and layer flocks with controlled feeding, housing and disease prevention.', ['Broilers', 'Layers', 'Eggs'], false],
                    ['gather', 'Veterinary check and feed store', 'Animal Health & Feed', 'Vaccination schedules, hygiene protocols and feed management that support productivity.', ['Veterinary', 'Feed', 'Monitoring'], true],
                ] as [$image, $alt, $title, $text, $tags, $navy])
                    <article class="card md:shadow-sm">
                        <x-static-picture :src="$image" :alt="$alt" sizes="(min-width: 1280px) 384px, (min-width: 768px) 45vw, 100vw" class="h-[170px] md:h-[210px]" />
                        <div class="p-5 md:p-[26px]">
                            <h3 class="text-[19px] md:text-[21px]">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-[1.65] text-ink-2 md:mt-2.5 md:text-[15px] md:leading-[1.7]">{{ $text }}</p>
                            <ul class="mt-[18px] flex flex-wrap gap-[7px]" aria-label="Focus areas">
                                @foreach ($tags as $tag)
                                    <li @class(['tag', 'tag-navy' => $navy])>{{ $tag }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-7 flex flex-col gap-3 rounded-card border border-line bg-surface px-5 py-5 md:flex-row md:items-center md:gap-[22px] md:px-[30px] md:py-[26px]">
                <p class="shrink-0 text-xs font-semibold tracking-[0.14em] text-ink-3 uppercase">Future divisions</p>
                <ul class="flex flex-wrap gap-2.5">
                    @foreach (['Goats', 'Sheep', 'Pigs', 'Fish'] as $division)
                        <li class="rounded-full border border-line px-3.5 py-[7px] text-[13px] font-medium text-ink-2">{{ $division }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="{{ route('livestock') }}" class="link-arrow mt-4 md:hidden">View livestock operations <span aria-hidden="true">→</span></a>
        </div>
    </section>

    {{-- 5 · Division B — Palm --}}
    <section class="bg-g900 py-10 text-white md:py-16 xl:py-24" aria-labelledby="division-b">
        <div class="container-site">
            <div class="grid items-center gap-5 xl:grid-cols-2 xl:gap-[72px]">
                <div class="order-2 h-[180px] overflow-hidden rounded-card md:h-[320px] xl:order-1 xl:h-[420px]">
                    <x-static-picture src="harvest" alt="Oil palm plantation rows in low sun" sizes="(min-width: 1280px) 600px, 100vw" class="h-full" />
                </div>
                <div class="order-1 xl:order-2">
                    <p class="eyebrow eyebrow-dark mb-3 md:mb-[18px]">Division B</p>
                    <h2 id="division-b" class="text-[27px] text-white md:text-[34px] xl:text-[40px]">Oil Palm Cultivation and Processing</h2>
                    <p class="mt-4 hidden max-w-[520px] text-base leading-[1.75] text-on-dark-2 md:block xl:mt-[22px]">From nursery and plantation establishment through harvesting, milling and distribution — one continuous production chain we manage end to end.</p>
                    <a href="{{ route('palm') }}" class="btn btn-ghost-light btn-lg mt-[30px] hidden border-white/50 md:inline-flex">Palm Production</a>
                </div>
            </div>
            <ol class="mt-6 flex flex-col border-white/16 md:grid md:grid-cols-4 md:gap-y-6 md:border-t md:pt-7 xl:mt-[60px] xl:grid-cols-7">
                @foreach (SharedContent::palmChain() as $index => [$step])
                    <li class="flex items-start gap-3.5 border-b border-white/12 py-3 last:border-b-0 md:block md:border-0 md:py-0 md:pr-4">
                        <span class="w-[22px] text-xs font-bold text-leaf">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="block text-[15px] font-semibold text-white md:mt-2">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
            <a href="{{ route('palm') }}" class="btn btn-ghost-light btn-lg mt-6 w-full border-white/50 md:hidden">Palm Production</a>
        </div>
    </section>

    {{-- 6 · Featured products --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="bg-bg py-10 md:bg-surface md:py-16 xl:py-24" aria-labelledby="featured-products">
            <div class="container-site">
                <div class="mb-5 flex items-end justify-between md:mb-11">
                    <div>
                        <p class="eyebrow mb-[18px] hidden md:block">Featured</p>
                        <h2 id="featured-products" class="text-[27px] md:text-[34px] xl:text-[40px]"><span class="md:hidden">Featured Products</span><span class="hidden md:inline">Agricultural Products</span></h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="link-arrow hidden md:inline-flex">All products <span aria-hidden="true">→</span></a>
                </div>
                <div class="grid grid-cols-2 gap-3.5 md:gap-6 xl:grid-cols-4">
                    @foreach ($featuredProducts as $product)
                        <x-product-card :product="$product" featured />
                    @endforeach
                </div>
                <div class="mt-4 grid gap-2 md:hidden">
                    <a href="{{ route('quote') }}" class="btn btn-primary btn-lg w-full">Request a Quote</a>
                    <a href="{{ route('products.index') }}" class="link-arrow justify-center">All products <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
    @endif

    {{-- 7 · Livestock production lifecycle --}}
    <section class="bg-sand py-10 md:py-16 xl:py-24" aria-labelledby="lifecycle">
        <div class="container-site">
            <p class="eyebrow mb-3 md:mb-[18px]">How We Farm</p>
            <h2 id="lifecycle" class="mb-5 text-[27px] md:mb-[52px] md:text-[34px] xl:text-[40px]">Livestock Production Lifecycle</h2>
            <x-lifecycle-steps :steps="SharedContent::livestockLifecycle()" />
        </div>
    </section>

    {{-- 8 · Sustainability + gallery teaser --}}
    <section class="bg-surface py-10 md:py-16 xl:py-24">
        <div class="container-site grid gap-12 xl:grid-cols-[1fr_1.15fr] xl:gap-[72px]">
            <div aria-labelledby="sustainability-teaser">
                <p class="eyebrow mb-3 md:mb-[18px]">Sustainability</p>
                <h2 id="sustainability-teaser" class="text-[27px] md:text-[36px]">Farming that the land can carry</h2>
                <div class="mt-6 flex flex-col gap-5 md:mt-[30px]">
                    <x-feature-note title="Animal welfare">Housing, handling and veterinary standards written into daily routine.</x-feature-note>
                    <x-feature-note title="Soil and water">Managed irrigation, cover management and waste handling on the plantation.</x-feature-note>
                    <x-feature-note title="Local employment">Farm labour, training and procurement drawn from surrounding communities.</x-feature-note>
                </div>
                <a href="{{ route('sustainability') }}" class="btn btn-outline btn-lg mt-8">Our commitments</a>
            </div>
            @if ($galleryTeaser->isNotEmpty())
                <div aria-labelledby="gallery-teaser">
                    <div class="mb-5 flex items-end justify-between">
                        <h3 id="gallery-teaser" class="text-[22px]">Farm Gallery</h3>
                        <a href="{{ route('gallery') }}" class="link-arrow text-sm">Open gallery <span aria-hidden="true">→</span></a>
                    </div>
                    <ul class="grid auto-rows-[110px] grid-cols-3 gap-2 md:auto-rows-[150px] md:gap-3">
                        @foreach ($galleryTeaser as $image)
                            <li>
                                <a href="{{ route('gallery') }}" class="block h-full overflow-hidden rounded-ui">
                                    <x-picture :media="$image->photo()" :alt="$image->title" sizes="(min-width: 1280px) 210px, 33vw" class="h-full" img-class="h-full w-full object-cover transition-transform duration-[400ms] hover:scale-[1.04]" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- 9 · Partnerships teaser --}}
    <section class="bg-navy py-[34px] md:py-[76px]" aria-labelledby="partners-teaser">
        <div class="container-site md:flex md:items-center md:gap-[60px]">
            <div class="flex-1">
                <h2 id="partners-teaser" class="max-w-[620px] text-2xl text-white md:text-[34px]">Growing Agricultural Value Through Strategic Partnerships</h2>
                <p class="mt-3.5 hidden max-w-[620px] text-base leading-[1.7] text-on-navy md:block">Distributors, retailers, veterinary services, feed suppliers, processors and institutional partners.</p>
            </div>
            <a href="{{ route('partners') }}" class="btn btn-light-navy btn-lg mt-[18px] w-full shrink-0 md:mt-0 md:w-auto">Become a Partner</a>
        </div>
    </section>

    {{-- 10 · Latest farm updates --}}
    @if ($posts->isNotEmpty())
        <section class="bg-bg py-10 md:py-16 xl:py-24" aria-labelledby="latest-updates">
            <div class="container-site">
                <div class="mb-6 flex items-end justify-between md:mb-11">
                    <h2 id="latest-updates" class="text-[27px] md:text-[36px]">Latest Farm Updates</h2>
                    <a href="{{ route('news.index') }}" class="link-arrow">All insights <span aria-hidden="true">→</span></a>
                </div>
                <div class="grid gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-post-card :post="$post" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 11 · Final CTA --}}
    <section class="bg-g900 py-10 md:py-[90px]" aria-labelledby="final-cta">
        <div class="container-site">
            <h2 id="final-cta" class="max-w-[720px] text-2xl text-white md:text-[42px]">Talk to us about supply, partnership or a farm visit</h2>
            <div class="mt-[18px] flex flex-col gap-3.5 md:mt-[30px] md:flex-row">
                <a href="{{ route('quote') }}" class="btn btn-light btn-lg">Request a Quote</a>
                <a href="{{ route('contact', ['interest' => 'farm_visit']) }}" class="btn btn-ghost-light btn-lg border-white/50">Visit Our Farm</a>
            </div>
        </div>
    </section>
</x-layouts.app>
