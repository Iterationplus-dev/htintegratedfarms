@php
    $phones = [$site->get('phone_primary'), $site->get('phone_secondary')];
    $mapUrl = $site->get('map_embed_url');
@endphp
<x-layouts.app title="Contact Us" description="Call, WhatsApp or message H & T Integrated Farms about supply, partnerships or a farm visit in Ogun State, Nigeria.">
    <section class="pb-[34px] md:pb-20">
        <div class="container-site grid items-start gap-0 pt-[26px] md:pt-[60px] xl:grid-cols-[1fr_1.2fr] xl:gap-[72px]">
            <div>
                <h1 class="text-[31px] md:text-[46px]">Contact Us</h1>
                <p class="mt-3.5 hidden text-[17px] leading-[1.75] text-ink-2 md:block">Sales, partnership and farm-visit inquiries all reach the same team.</p>

                <div class="mt-5 flex gap-2.5 md:hidden">
                    <a href="{{ $site->telHref($phones[0]) }}" class="btn btn-primary btn-lg flex-1"><x-ph name="phone" class="size-4" /> Call</a>
                    <a href="{{ $site->whatsappUrl('Hello H & T Integrated Farms') }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg flex-1"><x-ph name="whatsapp-logo" class="size-4" /> WhatsApp</a>
                </div>

                <dl class="mt-5 border-t border-line md:mt-[34px]">
                    <div class="border-b border-line py-4 md:py-5">
                        <dt class="text-[11px] font-semibold tracking-[0.12em] text-ink-3 uppercase">Telephone</dt>
                        @foreach ($phones as $phone)
                            <dd class="mt-0.5 first-of-type:mt-1.5"><a href="{{ $site->telHref($phone) }}" class="text-base font-semibold text-ink hover:text-g700 md:text-[17px]">{{ $phone }}</a></dd>
                        @endforeach
                    </div>
                    <div class="border-b border-line py-4 md:py-5">
                        <dt class="text-[11px] font-semibold tracking-[0.12em] text-ink-3 uppercase">United Kingdom</dt>
                        <dd class="mt-1.5"><a href="{{ $site->telHref($site->get('phone_uk')) }}" class="text-base font-semibold text-ink hover:text-g700 md:text-[17px]">{{ $site->get('phone_uk') }}</a></dd>
                    </div>
                    <div class="hidden border-b border-line py-5 md:block">
                        <dt class="text-[11px] font-semibold tracking-[0.12em] text-ink-3 uppercase">Email</dt>
                        <dd class="mt-1.5"><a href="mailto:{{ $site->get('email_info') }}" class="text-[17px] font-semibold text-ink hover:text-g700">{{ $site->get('email_info') }}</a></dd>
                    </div>
                    <div class="border-b border-line py-4 md:py-5">
                        <dt class="text-[11px] font-semibold tracking-[0.12em] text-ink-3 uppercase">Farm location</dt>
                        <dd class="mt-1.5 text-[15px] leading-[1.6] md:text-base md:leading-[1.7]">{!! nl2br(e($site->get('address'))) !!}</dd>
                    </div>
                    <div class="hidden py-5 md:block">
                        <dt class="text-[11px] font-semibold tracking-[0.12em] text-ink-3 uppercase">Working hours</dt>
                        <dd class="mt-1.5 text-base leading-[1.7]">{!! nl2br(e($site->get('working_hours'))) !!}</dd>
                    </div>
                </dl>
                <a href="{{ $site->whatsappUrl('Hello H & T Integrated Farms') }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg mt-3 hidden md:inline-flex"><x-ph name="whatsapp-logo" class="size-5" /> Chat on WhatsApp</a>
            </div>

            <div class="mt-[26px] xl:mt-0">
                <livewire:contact-form :interest="$interest" :product="$product" />

                {{-- Map: click-to-load (Google Maps sets third-party cookies). --}}
                <div x-data="{ load: false }" class="mt-6 h-[220px] overflow-hidden rounded-card border border-line bg-sand md:h-[280px]">
                    @if ($mapUrl !== '')
                        <template x-if="load">
                            <iframe src="{{ $mapUrl }}" title="Map showing the H & T farm location" class="h-full w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </template>
                    @endif
                    <div x-show="!load" class="flex h-full flex-col items-center justify-center gap-3 px-6 text-center">
                        <x-ph name="map-trifold" class="size-8 text-g600" />
                        <p class="text-sm font-medium text-ink-3">Farm location, Ogun State</p>
                        <div class="flex flex-wrap justify-center gap-2.5">
                            @if ($mapUrl !== '')
                                <button type="button" @click="load = true" class="btn btn-outline bg-surface">Show map</button>
                            @endif
                            <a href="{{ $site->get('map_directions_url') }}" target="_blank" rel="noopener" class="btn btn-outline bg-surface">Get directions <x-ph name="arrow-up-right" class="size-4" /></a>
                        </div>
                        @if ($mapUrl !== '')
                            <p class="text-xs text-ink-3">Loading the map shares data with Google.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
