@php use App\Support\SharedContent; @endphp
<x-layouts.app title="Livestock Production" description="Cattle and poultry raised under recorded management, veterinary supervision and controlled biosecurity at H & T Integrated Farms, Ogun State.">
    <x-image-hero image="cattle" alt="Herd in managed pasture" crumb="Livestock"
                  title="Professional Livestock Production & Management"
                  lead="Cattle and poultry raised under recorded management, veterinary supervision and controlled biosecurity."
                  mobile-lead="Cattle and poultry under recorded management and veterinary supervision." />

    <section class="bg-surface py-[34px] md:py-[90px]" aria-labelledby="herd-areas">
        <div class="container-site">
            <p class="eyebrow mb-3 md:mb-4">Cattle Production</p>
            <h2 id="herd-areas" class="mb-5 text-[25px] md:mb-10 md:text-[36px]">Six areas we manage on the herd</h2>
            <ol class="grid grid-cols-2 gap-3 md:gap-5 xl:grid-cols-3">
                @foreach ([
                    ['Breeding', 'Breeding', 'Selected stock, recorded lineage and planned calving intervals.'],
                    ['Nutrition', 'Nutrition', 'Rations set by growth stage, with feed use recorded per pen.'],
                    ['Housing', 'Housing', 'Shaded, ventilated shelters with controlled stocking density.'],
                    ['Health', 'Health', 'Vaccination schedules and veterinary review on a fixed cycle.'],
                    ['Biosecurity', 'Biosecurity', 'Controlled access, disinfection points and quarantine for new stock.'],
                    ['Growth monitoring', 'Growth', 'Weight tracked to target so supply can be scheduled accurately.'],
                ] as $index => [$title, $short, $text])
                    <li class="rounded-ui border border-line p-4 md:rounded-card md:p-7">
                        <span class="text-xs font-bold text-g600 md:flex md:size-10 md:items-center md:justify-center md:rounded-ui md:bg-g100 md:text-[15px] md:text-g700">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-2 text-base md:mt-[18px] md:text-[19px]"><span class="md:hidden">{{ $short }}</span><span class="hidden md:inline">{{ $title }}</span></h3>
                        <p class="mt-2 hidden text-sm leading-[1.7] text-ink-2 md:block">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="bg-bg py-[34px] md:py-[90px]" aria-labelledby="poultry">
        <div class="container-site grid items-center gap-6 xl:grid-cols-[1.1fr_1fr] xl:gap-[72px]">
            <div>
                <p class="eyebrow mb-3 md:mb-4">Poultry Production</p>
                <h2 id="poultry" class="text-[25px] md:text-[36px]">Broilers, layers and table eggs</h2>
                <div class="mt-[18px] h-[170px] overflow-hidden rounded-card md:hidden">
                    <x-static-picture src="chicks" alt="Broiler house" sizes="100vw" class="h-full" />
                </div>
                <p class="mt-4 max-w-[520px] text-[15px] leading-[1.7] text-ink-2 md:text-base md:leading-[1.75]">Flocks are run in batches with feeding, housing and health management planned around each cycle, so buyers can be given firm dates and volumes.</p>
                <div class="mt-[30px] hidden gap-[18px] md:grid md:grid-cols-2">
                    @foreach ([['Broilers', 'Batch-reared to market weight'], ['Layers', 'Managed for consistent lay rate'], ['Eggs', 'Collected, graded and crated daily'], ['Feed', 'Stored and mixed on site']] as [$title, $text])
                        <div class="rounded-ui border border-line bg-surface p-[18px]">
                            <p class="text-[15px] font-bold">{{ $title }}</p>
                            <p class="mt-1 text-sm leading-[1.6] text-ink-2">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('poultry') }}" class="link-arrow mt-5">Poultry production <span aria-hidden="true">→</span></a>
            </div>
            <div class="hidden grid-rows-[200px_200px] gap-4 md:grid">
                <x-static-picture src="chicks" alt="Broiler house" sizes="(min-width: 1280px) 560px, 100vw" class="overflow-hidden rounded-ui" />
                <x-static-picture src="gather" alt="Egg collection" sizes="(min-width: 1280px) 560px, 100vw" class="overflow-hidden rounded-ui" />
            </div>
        </div>
    </section>

    <section class="bg-navy py-[34px] text-white md:py-[90px]" aria-labelledby="biosecurity">
        <div class="container-site grid gap-6 xl:grid-cols-[1fr_1.4fr] xl:gap-[72px]">
            <div>
                <p class="eyebrow mb-3 text-on-navy-accent md:mb-4"><span class="md:hidden">Biosecurity</span><span class="hidden md:inline">Farm Health &amp; Biosecurity</span></p>
                <h2 id="biosecurity" class="text-2xl text-white md:text-[34px]">Disease prevention is a routine, not a response</h2>
            </div>
            <p class="text-sm leading-[2] text-on-navy md:hidden">Controlled access · Veterinary monitoring · Vaccination · Hygiene · Quarantine</p>
            <div class="hidden gap-[26px] md:grid md:grid-cols-2">
                @foreach ([
                    ['Controlled farm access', 'Visitors logged, vehicles disinfected, restricted production zones.'],
                    ['Veterinary monitoring', 'Regular inspection with records kept per unit.'],
                    ['Vaccination', 'Programmes scheduled by species and age group.'],
                    ['Hygiene', 'Cleaning cycles between batches and litter management.'],
                ] as [$title, $text])
                    <div>
                        <h3 class="text-base text-white">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm leading-[1.7] text-on-navy">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-sand py-[34px] md:py-[90px]" aria-labelledby="cycle">
        <div class="container-site">
            <h2 id="cycle" class="mb-[18px] text-2xl md:mb-[46px] md:text-[36px]"><span class="md:hidden">Production Cycle</span><span class="hidden md:inline">Livestock Production Cycle</span></h2>
            <x-lifecycle-steps :steps="SharedContent::livestockLifecycle()" :with-descriptions="false" />
        </div>
    </section>

    <x-cta-band title="Need livestock supplied on a schedule?" :href="route('quote')" />
</x-layouts.app>
