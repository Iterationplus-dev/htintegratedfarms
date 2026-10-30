@php use App\Support\Navigation; @endphp
<footer class="bg-footer text-on-dark-2">
    <div class="container-site pt-[34px] pb-[26px] md:pt-16 md:pb-[34px]">
        <div class="grid gap-8 md:grid-cols-2 md:gap-11 xl:grid-cols-[1.6fr_1fr_1fr_1.3fr]">
            <div>
                <p class="text-[17px] font-extrabold tracking-[-0.02em] text-white md:text-xl">{{ $site->get('company_name') }}</p>
                <p class="mt-3 hidden max-w-[320px] text-sm leading-[1.7] md:block">{{ $site->get('tagline') }}</p>
                <a href="{{ route('home') }}" class="mt-2 inline-block text-[13px] font-semibold text-leaf hover:text-white md:mt-4 md:text-sm">{{ $site->get('domain_label') }}</a>
            </div>

            @foreach (Navigation::footer() as $heading => $links)
                <nav aria-label="{{ $heading }}" class="hidden md:block">
                    <h2 class="mb-2.5 text-xs font-semibold tracking-[0.14em] text-footer-muted uppercase">{{ $heading }}</h2>
                    <ul class="text-sm leading-[2.1]">
                        @foreach ($links as $link)
                            <li><a href="{{ route($link['route']) }}" class="text-on-dark-2 hover:text-white">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach

            <div>
                <h2 class="mb-2.5 hidden text-xs font-semibold tracking-[0.14em] text-footer-muted uppercase md:block">Contact</h2>
                <ul class="text-sm leading-[2] md:leading-[2.1]">
                    <li><a href="{{ $site->telHref($site->get('phone_primary')) }}" class="text-on-dark-2 hover:text-white">{{ $site->get('phone_primary') }}</a></li>
                    <li><a href="{{ $site->telHref($site->get('phone_secondary')) }}" class="text-on-dark-2 hover:text-white">{{ $site->get('phone_secondary') }}</a></li>
                    <li><a href="{{ $site->telHref($site->get('phone_uk')) }}" class="text-on-dark-2 hover:text-white">UK {{ $site->get('phone_uk') }}</a></li>
                    <li class="hidden md:list-item"><a href="mailto:{{ $site->get('email_info') }}" class="text-on-dark-2 hover:text-white">{{ $site->get('email_info') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-5 flex flex-col gap-3 border-t border-white/12 pt-3.5 text-xs text-footer-muted md:mt-10 md:flex-row md:items-center md:justify-between md:pt-5 md:text-[13px]">
            <p>© {{ now()->year }} {{ $site->get('company_name') }}</p>
            <ul class="flex flex-wrap gap-x-[22px] gap-y-1">
                <li><a href="{{ route('privacy') }}" class="text-footer-muted hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}" class="text-footer-muted hover:text-white">Terms</a></li>
                <li><a href="{{ route('cookies') }}" class="text-footer-muted hover:text-white">Cookie Policy</a></li>
                <li><button type="button" x-data @click="$store.consent.openPreferences()" class="cursor-pointer text-footer-muted hover:text-white">Cookie settings</button></li>
                <li><a href="{{ route('sitemap') }}" class="text-footer-muted hover:text-white">Sitemap</a></li>
            </ul>
        </div>
    </div>
</footer>
