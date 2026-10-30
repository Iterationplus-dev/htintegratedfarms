<x-layouts.app title="Sustainability" description="The responsible farming practices H & T Integrated Farms operates today: animal welfare, feed, waste, soil and water management, and local employment.">
    <section class="bg-g900 py-[34px] md:py-20">
        <div class="container-site grid items-center gap-[18px] xl:grid-cols-[1.2fr_1fr] xl:gap-[72px]">
            <div>
                <x-breadcrumb dark :items="[['Sustainability', null]]" class="mb-4 hidden md:block" />
                <h1 class="max-w-[600px] text-[31px] text-white md:text-[44px] xl:text-[50px]">Responsible farming, stated plainly</h1>
                <p class="mt-3 max-w-[560px] text-[15px] leading-[1.7] text-on-dark-2 md:mt-[18px] md:text-[17px] md:leading-[1.75]">
                    <span class="md:hidden">The practices we operate today. We avoid claims we cannot support.</span>
                    <span class="hidden md:inline">These are the practices we operate today. We publish what we measure and avoid claims we cannot support.</span>
                </p>
            </div>
            <div class="h-[180px] overflow-hidden rounded-card md:h-[320px]">
                <x-static-picture src="seedlings" alt="Plantation cover crop and soil" sizes="(min-width: 1280px) 530px, 100vw" priority class="h-full" />
            </div>
        </div>
    </section>

    <section class="bg-surface py-[30px] md:py-[90px]" aria-label="Our commitments">
        <ul class="container-site grid gap-[22px] md:grid-cols-2 md:gap-6 xl:grid-cols-3">
            @foreach ([
                ['Animal welfare', 'border-g500', 'Housing, stocking density, handling and veterinary care set as standing routine rather than exception.'],
                ['Sustainable feed practice', 'border-g500', 'Feed sourced and mixed to reduce waste, with usage recorded against each production unit.'],
                ['Waste management', 'border-g500', 'Litter and processing residue handled through defined routes, returned to land where appropriate.'],
                ['Soil protection', 'border-sky', 'Cover management and fertilisation planned to maintain plantation soil condition over successive cycles.'],
                ['Water management', 'border-sky', 'Controlled supply for livestock and irrigation, with drainage managed across the plantation.'],
                ['Local employment', 'border-leaf', 'Farm labour, training and procurement drawn from surrounding communities.'],
            ] as [$title, $border, $text])
                <li class="border-t-2 {{ $border }} pt-4 md:pt-[22px]">
                    <h2 class="text-[19px] md:text-[21px]">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-[1.7] text-ink-2 md:mt-2.5 md:text-[15px] md:leading-[1.75]">{{ $text }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    @if ($stats->isNotEmpty())
        <section class="bg-sand py-[34px] md:py-[70px]" aria-label="What we measure">
            <dl class="container-site grid grid-cols-2 gap-6 xl:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="flex flex-col-reverse">
                        <dt class="mt-1.5 text-[13px] font-medium text-ink-2">{{ $stat->label }}</dt>
                        <dd class="text-[30px] font-extrabold text-g900 md:text-[40px]">{{ $stat->value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    <x-cta-band title="Want to see these practices on the ground?" :href="route('contact', ['interest' => 'farm_visit'])" label="Request a Farm Visit" />
</x-layouts.app>
