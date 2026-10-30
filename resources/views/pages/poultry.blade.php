@php use App\Support\SharedContent; @endphp
{{-- Poultry Production: in the nav but not designed in the handoff — composed from existing sections and copy. --}}
<x-layouts.app title="Poultry Production" description="Broilers, layers, point-of-lay birds and table eggs from H & T Integrated Farms: batch-run flocks with scheduled feeding, clean water and veterinary oversight.">
    <x-image-hero image="hero-poultry-field" alt="Farm staff feeding free-range chickens" crumb="Poultry"
                  title="Poultry and Egg Production"
                  lead="Broiler and layer flocks run in batches, with scheduled feeding, clean water and veterinary oversight across our poultry houses and open runs."
                  mobile-lead="Batch-run flocks with scheduled feeding, clean water and veterinary oversight." />

    <section class="bg-surface py-[34px] md:py-[90px]" aria-labelledby="flocks">
        <div class="container-site grid items-center gap-8 xl:grid-cols-[1.1fr_1fr] xl:gap-[72px]">
            <div>
                <p class="eyebrow mb-3 md:mb-4">How we run our flocks</p>
                <h2 id="flocks" class="text-[25px] md:text-[36px]">Broilers, layers and table eggs</h2>
                <p class="mt-4 max-w-[520px] text-[15px] leading-[1.7] text-ink-2 md:text-base md:leading-[1.75]">Flocks are run in batches with feeding, housing and health management planned around each cycle, so buyers can be given firm dates and volumes.</p>
                <div class="mt-[30px] grid gap-3 sm:grid-cols-2 md:gap-[18px]">
                    @foreach ([['Broilers', 'Batch-reared to market weight'], ['Layers', 'Managed for consistent lay rate'], ['Eggs', 'Collected, graded and crated daily'], ['Feed', 'Stored and mixed on site']] as [$title, $text])
                        <div class="rounded-ui border border-line bg-bg p-[18px]">
                            <p class="text-[15px] font-bold">{{ $title }}</p>
                            <p class="mt-1 text-sm leading-[1.6] text-ink-2">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 grid-rows-[150px_150px] gap-3 md:grid-rows-[200px_200px] md:gap-4">
                <x-static-picture src="hero-poultry-house" alt="Laying hens at a feed trough inside a poultry house" sizes="(min-width: 1280px) 280px, 50vw" class="row-span-2 overflow-hidden rounded-ui" />
                <x-static-picture src="chicks" alt="Day-old chicks" sizes="(min-width: 1280px) 280px, 50vw" class="overflow-hidden rounded-ui" />
                <x-static-picture src="eggs" alt="Graded eggs in trays" sizes="(min-width: 1280px) 280px, 50vw" class="overflow-hidden rounded-ui" />
            </div>
        </div>
    </section>

    <section class="bg-navy py-[34px] md:py-[90px]" aria-labelledby="flock-health">
        <div class="container-site grid gap-6 xl:grid-cols-[1fr_1.4fr] xl:gap-[72px]">
            <div>
                <p class="eyebrow mb-3 text-on-navy-accent md:mb-4">Flock Health</p>
                <h2 id="flock-health" class="text-2xl text-white md:text-[34px]">Disease prevention is a routine, not a response</h2>
            </div>
            <div class="grid gap-5 md:grid-cols-2 md:gap-[26px]">
                @foreach ([
                    ['Controlled access', 'Visitors logged, vehicles disinfected, restricted production zones.'],
                    ['Vaccination', 'Programmes scheduled by age group, with records kept per house.'],
                    ['Hygiene between batches', 'Cleaning cycles and litter management before every new flock.'],
                    ['Daily checks', 'Feed and water intake, mortality and lay rate recorded every day.'],
                ] as [$title, $text])
                    <div>
                        <h3 class="text-base text-white">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm leading-[1.7] text-on-navy">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($products->isNotEmpty())
        <section class="bg-bg py-[34px] md:py-[90px]" aria-labelledby="poultry-products">
            <div class="container-site">
                <div class="mb-5 flex items-end justify-between md:mb-11">
                    <h2 id="poultry-products" class="text-[25px] md:text-[36px]">Poultry products</h2>
                    <a href="{{ route('products.index') }}" class="link-arrow">All products <span aria-hidden="true">→</span></a>
                </div>
                <div class="grid grid-cols-2 gap-3.5 md:gap-6 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="bg-sand py-[34px] md:py-[90px]" aria-labelledby="cycle">
        <div class="container-site">
            <h2 id="cycle" class="mb-[18px] text-2xl md:mb-[46px] md:text-[36px]">Production Cycle</h2>
            <x-lifecycle-steps :steps="SharedContent::livestockLifecycle()" />
        </div>
    </section>

    <x-cta-band title="Need eggs or birds supplied on a schedule?" :href="route('quote', ['product' => 'table-eggs'])" />
</x-layouts.app>
