@php use App\Support\SharedContent; @endphp
<x-layouts.app title="Palm Production" description="Oil palm cultivation, harvesting and processing: one continuous chain from nursery to distribution, managed on the H & T plantation in Ogun State.">
    <x-image-hero image="grove" alt="Oil palm plantation, avenue of trees" crumb="Palm Production" tall
                  title="Oil Palm Cultivation, Harvesting and Processing"
                  lead="One continuous chain from nursery to distribution, managed on our own plantation." />

    <section class="bg-surface py-[34px] md:py-[90px]" aria-labelledby="palm-lifecycle">
        <div class="container-site">
            <h2 id="palm-lifecycle" class="mb-[18px] text-[25px] md:mb-3 md:text-[36px]"><span class="md:hidden">Production lifecycle</span><span class="hidden md:inline">The palm production lifecycle</span></h2>
            <p class="mb-12 hidden max-w-[620px] text-base leading-[1.7] text-ink-2 md:block">Each stage is managed and recorded, which is what lets us commit to volumes and delivery dates.</p>
            <ol class="flex flex-col gap-2.5 md:grid md:grid-cols-4 md:items-stretch md:gap-3 xl:grid-cols-7">
                @foreach (SharedContent::palmChain() as $index => [$title, $text, $accent])
                    @php $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); @endphp
                    <li @class([
                        'rounded-ui p-4 md:rounded-card md:px-[18px] md:py-[22px]',
                        'border-l-[3px] bg-bg md:border-t-[3px] md:border-l-0' => $accent !== 'navy',
                        'border-g500' => $accent === 'green',
                        'border-sky' => $accent === 'sky',
                        'bg-navy' => $accent === 'navy',
                    ])>
                        <p @class(['text-xs font-bold', 'text-g600' => $accent === 'green', 'text-navy-600' => $accent === 'sky', 'text-on-navy-accent' => $accent === 'navy'])>
                            {{ $number }}<span class="md:hidden"> · {{ $title }}</span>
                        </p>
                        <h3 @class(['mt-2.5 hidden text-base md:block', 'text-white' => $accent === 'navy'])>{{ $title }}</h3>
                        <p @class(['mt-1 text-sm leading-[1.6] md:mt-2 md:text-[13px]', 'text-ink-2' => $accent !== 'navy', 'text-on-navy' => $accent === 'navy'])>{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="hidden bg-bg py-[90px] md:block" aria-label="Palm operations">
        <div class="container-site grid gap-6 md:grid-cols-3">
            @foreach ([
                ['seedlings', 'Palm nursery', 'Palm Nursery', 'Seedling development and selection before field planting.'],
                ['avenue', 'Plantation maintenance', 'Plantation Management', 'Tree health, fertilisation, weed control and yield planning.'],
                ['harvest', 'Harvesting fresh fruit bunches', 'Harvesting', 'Cutting rounds scheduled to bunch maturity, with careful handling.'],
            ] as [$image, $alt, $title, $text])
                <article class="card">
                    <x-static-picture :src="$image" :alt="$alt" sizes="(min-width: 1280px) 384px, 33vw" class="h-[200px]" />
                    <div class="p-[26px]">
                        <h3 class="text-[21px]">{{ $title }}</h3>
                        <p class="mt-2.5 text-[15px] leading-[1.7] text-ink-2">{{ $text }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="bg-g900 py-[34px] md:py-[90px]" aria-labelledby="processing">
        <div class="container-site grid items-center gap-[18px] xl:grid-cols-2 xl:gap-[72px]">
            <div>
                <p class="eyebrow eyebrow-dark mb-4 hidden md:block">Processing</p>
                <h2 id="processing" class="text-2xl text-white md:text-[36px]">From fresh fruit bunch to packaged oil</h2>
                <p class="mt-4 hidden max-w-[500px] text-base leading-[1.75] text-on-dark-2 md:block">Fruit is processed promptly after harvest to protect quality. Output is clarified, stored and packaged for bulk or retail supply, with palm kernels recovered as a second product line.</p>
                @if ($stats->isNotEmpty())
                    <dl class="mt-8 hidden gap-[30px] border-t border-white/16 pt-6 md:flex">
                        @foreach ($stats as $stat)
                            <div class="flex flex-col-reverse">
                                <dt class="mt-1 text-[13px] font-medium text-on-dark-accent">{{ $stat->label }}</dt>
                                <dd class="text-[30px] font-extrabold text-white">{{ $stat->value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>
            <div class="h-[180px] overflow-hidden rounded-card md:h-[320px] xl:h-[400px]">
                <x-static-picture src="oil" alt="H & T branded palm oil containers from the mill" sizes="(min-width: 1280px) 600px, 100vw" class="h-full" />
            </div>
            <a href="{{ route('quote', ['product' => 'palm-oil']) }}" class="btn btn-light btn-lg w-full md:hidden">Request a Quote</a>
        </div>
    </section>

    <div class="hidden md:block">
        <x-cta-band title="Buying palm oil, kernels or fresh fruit bunches?" :href="route('quote', ['product' => 'palm-oil'])" tone="sand" />
    </div>
</x-layouts.app>
