@php use App\Enums\PartnershipType; @endphp
<x-layouts.app title="Partnerships" description="Distributors, retailers, suppliers, veterinary services, processors, investors and institutions: partner with H & T Integrated Farms.">
    <section class="bg-navy py-[34px] md:py-20">
        <div class="container-site">
            <nav aria-label="Breadcrumb" class="mb-4 hidden text-[13px] text-on-navy-accent md:block">
                <a href="{{ route('home') }}" class="text-on-navy-accent hover:text-white">Home</a> / <span aria-current="page">Partnerships</span>
            </nav>
            <h1 class="max-w-[760px] text-[30px] text-white md:text-[44px] xl:text-[50px]">Growing Agricultural Value Through Strategic Partnerships</h1>
            <p class="mt-3 max-w-[620px] text-[15px] leading-[1.7] text-on-navy md:mt-[18px] md:text-[17px] md:leading-[1.75]">
                <span class="md:hidden">Partners across input supply, distribution and finance.</span>
                <span class="hidden md:inline">We work with organisations across the agricultural chain — from input supply through to distribution and finance.</span>
            </p>
        </div>
    </section>

    <section class="py-[26px] md:py-20" aria-label="Who we partner with">
        <ul class="container-site grid grid-cols-2 gap-2.5 md:grid-cols-3 md:gap-[18px] xl:grid-cols-5">
            @foreach ($types as $type)
                <li @class([
                    'rounded-ui border border-line p-4 md:rounded-card md:p-6',
                    'hidden md:block' => ! in_array($type, [PartnershipType::Distributor, PartnershipType::Retailer, PartnershipType::FeedSupplier, PartnershipType::VeterinaryServices, PartnershipType::Processor, PartnershipType::Investor], true),
                ])>
                    <h2 class="text-[15px] md:text-[17px]"><span class="md:hidden">{{ $type->shortLabel() }}</span><span class="hidden md:inline">{{ $type->getLabel() }}</span></h2>
                    <p class="mt-2 hidden text-[13px] leading-[1.65] text-ink-2 md:block">{{ $type->description() }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="pb-[34px] md:pb-[90px]" aria-labelledby="become-partner">
        <div class="container-site grid items-start gap-5 xl:grid-cols-[1fr_1.1fr] xl:gap-[72px]">
            <div>
                <h2 id="become-partner" class="text-[25px] md:text-[34px]">Become a Partner</h2>
                <p class="mt-3.5 hidden text-base leading-[1.75] text-ink-2 md:block">Tell us what you are looking for. Inquiries are reviewed by the management team and answered directly.</p>
                <div class="mt-[26px] hidden rounded-card bg-bg p-[26px] text-[15px] leading-[1.9] text-ink-2 md:block">
                    <p class="mb-2 text-[15px] font-bold text-ink">Direct contact</p>
                    <a href="{{ $site->telHref($site->get('phone_primary')) }}" class="block text-ink-2 hover:text-g700">{{ $site->get('phone_primary') }}</a>
                    <a href="{{ $site->telHref($site->get('phone_secondary')) }}" class="block text-ink-2 hover:text-g700">{{ $site->get('phone_secondary') }}</a>
                    <a href="{{ $site->telHref($site->get('phone_uk')) }}" class="block text-ink-2 hover:text-g700">United Kingdom {{ $site->get('phone_uk') }}</a>
                    <a href="mailto:{{ $site->get('email_partners') }}" class="block text-ink-2 hover:text-g700">{{ $site->get('email_partners') }}</a>
                </div>
            </div>
            <livewire:partnership-form />
        </div>
    </section>
</x-layouts.app>
