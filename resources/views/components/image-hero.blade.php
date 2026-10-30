@props(['image', 'alt', 'title', 'lead' => null, 'mobileLead' => null, 'crumb', 'tall' => false])
{{-- Full-bleed photo hero (handoff §03 / §04): horizontal gradient on desktop, bottom-anchored text on mobile. --}}
<section @class(['relative h-[340px] overflow-hidden bg-g900', 'md:h-[480px]' => $tall, 'md:h-[440px]' => ! $tall])>
    <x-static-picture :src="$image" :alt="$alt" sizes="100vw" priority class="h-full" />
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(9,32,18,.3),rgba(9,32,18,.92))] md:bg-[linear-gradient(90deg,rgba(9,32,18,.9),rgba(9,32,18,.15))]"></div>
    <div class="absolute inset-0 flex flex-col justify-end md:justify-center">
        <div class="container-site pb-5 md:pb-0">
            <x-breadcrumb dark :items="[[$crumb, null]]" class="mb-4 hidden md:block" />
            <h1 class="max-w-[720px] text-[30px] text-white md:text-[44px] xl:text-[50px]">{{ $title }}</h1>
            @if ($lead)
                <p class="mt-2.5 max-w-[600px] text-sm leading-[1.65] text-on-dark md:mt-4 md:text-[17px] md:leading-[1.7]">
                    @if ($mobileLead)<span class="md:hidden">{{ $mobileLead }}</span><span class="hidden md:inline">{{ $lead }}</span>@else{{ $lead }}@endif
                </p>
            @endif
        </div>
    </div>
</section>
