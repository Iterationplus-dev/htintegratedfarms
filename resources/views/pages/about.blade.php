<x-layouts.app title="About Us" description="H & T Integrated Farms Limited operates livestock, poultry and oil palm production from a single managed base in Ogun State, Nigeria.">
    <div class="bg-bg">
        <div class="container-site py-3.5 md:py-[26px]"><x-breadcrumb :items="[['About Us', null]]" /></div>
    </div>

    <section class="bg-bg pb-[34px] md:pb-[90px]">
        <div class="container-site grid items-center gap-5 pt-4 md:pt-10 xl:grid-cols-2 xl:gap-[72px] xl:pt-[50px]">
            <div>
                <h1 class="max-w-[540px] text-[32px] md:text-[44px] xl:text-[52px]">An agricultural company built for the long term</h1>
                <p class="mt-3.5 max-w-[520px] text-[15px] leading-[1.7] text-ink-2 md:mt-[22px] md:text-[17px] md:leading-[1.75]">
                    <span class="md:hidden">Livestock, poultry and oil palm production from a single managed base in Ogun State.</span>
                    <span class="hidden md:inline">H &amp; T Integrated Farms Limited operates livestock, poultry and oil palm production from a single managed base in Ogun State, supplying consumers, commercial buyers and distributors.</span>
                </p>
            </div>
            <div class="h-[200px] overflow-hidden rounded-card md:h-[340px] xl:h-[380px]">
                <x-static-picture src="farm" alt="Wide farm landscape with buildings" sizes="(min-width: 1280px) 600px, 100vw" priority class="h-full" />
            </div>
        </div>
    </section>

    @if ($stats->isNotEmpty())
        <section aria-label="H & T in numbers" class="bg-g900">
            <dl class="container-site grid grid-cols-2 gap-5 py-[30px] md:grid-cols-4 md:gap-6 md:py-16">
                @foreach ($stats as $stat)
                    <div class="flex flex-col-reverse">
                        <dt class="mt-1 text-xs font-medium text-on-dark-accent md:mt-1.5 md:text-[13px]"><span class="md:hidden">{{ $stat->compactLabel() }}</span><span class="hidden md:inline">{{ $stat->label }}</span></dt>
                        <dd class="text-[30px] font-extrabold text-white md:text-[40px] md:tracking-[-0.03em] xl:text-[46px]">{{ $stat->value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    <section class="bg-surface py-[34px] md:py-[90px]">
        <div class="container-site grid gap-3 xl:grid-cols-[320px_1fr] xl:gap-[72px]">
            <p class="eyebrow">Our Story</p>
            <div class="max-w-[720px]">
                <h2 class="text-[25px] md:text-[34px]">From a single production unit to an integrated farm</h2>
                {{-- Placeholder narrative from the handoff — awaiting the client's founding account. --}}
                <p class="mt-3 text-[15px] leading-[1.75] text-ink-2 md:mt-[18px] md:text-base md:leading-[1.8]">H &amp; T began with a single production unit and has grown into an integrated farm that runs livestock and oil palm side by side, so that each part of the operation supports the others — feed, land, labour and supply planned as one business.</p>
                <p class="mt-3.5 text-[15px] leading-[1.75] text-ink-2 md:text-base md:leading-[1.8]">Today the farm runs cattle, poultry and egg production alongside a managed oil palm plantation and processing, with goats, sheep, pigs and fish identified as the next divisions.</p>
            </div>
        </div>
        <div class="container-site mt-6 grid gap-3.5 md:mt-[90px] md:grid-cols-3 md:gap-6">
            @foreach ([
                ['Vision', 'border-g500', 'To be a leading integrated agricultural producer in Nigeria, supplying livestock and palm resources at dependable commercial scale.'],
                ['Mission', 'border-sky', 'To produce healthy livestock and quality palm products through modern farm management, responsible practice and consistent supply.'],
                ['Core Values', 'border-leaf', 'Productivity · Animal welfare · Sustainability · Integrity · Community'],
            ] as [$heading, $border, $text])
                <div class="rounded-card border-t-[3px] {{ $border }} bg-bg p-[22px] md:p-[34px]">
                    <h3 class="text-lg md:text-[21px]">{{ $heading }}</h3>
                    <p class="mt-2 text-sm leading-[1.7] text-ink-2 md:mt-3 md:text-[15px] md:leading-[1.75]">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-sand py-[34px] md:py-[90px]">
        <div class="container-site grid items-center gap-8 xl:grid-cols-2 xl:gap-[72px]">
            <div class="h-[220px] overflow-hidden rounded-card md:h-[340px]">
                <x-static-picture src="team" alt="Management team on site" sizes="(min-width: 1280px) 600px, 100vw" class="h-full" />
            </div>
            <div>
                <p class="eyebrow mb-4">Management Approach</p>
                <h2 class="text-[25px] md:text-[34px]">Recorded, scheduled, supervised</h2>
                <div class="mt-[26px] flex flex-col gap-[18px]">
                    <x-feature-note title="Production records">Stock counts, feed use and yields captured against each unit.</x-feature-note>
                    <x-feature-note title="Veterinary oversight">Scheduled vaccination, monitoring and intervention protocols.</x-feature-note>
                    <x-feature-note title="Agricultural philosophy">Yield that does not come at the cost of soil, water or animal condition.</x-feature-note>
                </div>
            </div>
        </div>
    </section>

    <x-cta-band title="Come and see the operation for yourself" :href="route('contact', ['interest' => 'farm_visit'])" label="Request a Farm Visit" stacked />
</x-layouts.app>
