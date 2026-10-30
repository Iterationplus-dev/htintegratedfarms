@php
    use App\Support\Navigation;
    $items = Navigation::primary();
@endphp
<header x-data="mobileNav" class="relative z-40 border-b border-line bg-surface">
    {{-- Desktop (≥1280) --}}
    <div class="container-site hidden items-center gap-[34px] py-4 xl:flex">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="H & T Integrated Farms — home">
            <picture>
                <source type="image/webp" srcset="{{ Vite::asset('resources/images/ht-logo.webp') }}">
                <img src="{{ Vite::asset('resources/images/ht-logo.png') }}" alt="H and T Integrated Farms Limited" width="140" height="52" class="h-[52px] w-[140px] object-contain object-left mix-blend-multiply">
            </picture>
        </a>
        <nav aria-label="Main" class="ml-auto">
            <ul class="flex items-center gap-6 text-[15px] font-medium whitespace-nowrap text-ink-2">
                @foreach ($items as $item)
                    @php $active = Navigation::isActive($item['active']); @endphp
                    <li>
                        <a href="{{ Navigation::href($item['route']) }}"
                           @if ($active) aria-current="page" @endif
                           @class([
                               'block transition-colors duration-150',
                               'border-b-[3px] border-g500 pb-[3px] font-semibold text-g700 shadow-nav-active hover:text-g700' => $active,
                               'border-b-2 border-transparent pb-1.5 text-ink-2 hover:text-g700' => ! $active,
                           ])>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
        <a href="{{ route('quote') }}" class="btn btn-primary">Request a Quote</a>
    </div>

    {{-- Mobile / tablet (<1280) --}}
    <div class="container-site flex items-center justify-between py-3 xl:hidden">
        <a href="{{ route('home') }}" aria-label="H & T Integrated Farms — home">
            <picture>
                <source type="image/webp" srcset="{{ Vite::asset('resources/images/ht-logo.webp') }}">
                <img src="{{ Vite::asset('resources/images/ht-logo.png') }}" alt="H and T Integrated Farms Limited" width="110" height="40" class="h-10 w-[110px] object-contain object-left mix-blend-multiply">
            </picture>
        </a>
        <div class="flex items-center gap-2.5">
            <a href="{{ $site->telHref($site->get('phone_primary')) }}" class="flex size-11 items-center justify-center rounded-ui border border-line text-g700 hover:bg-bg" aria-label="Call {{ $site->get('phone_primary') }}">
                <x-ph name="phone" class="size-5" />
            </a>
            <button type="button" x-ref="menuButton" @click="toggle()" class="flex size-11 cursor-pointer flex-col items-center justify-center gap-1 rounded-ui border border-line hover:bg-bg"
                    aria-controls="mobile-menu" :aria-expanded="open.toString()" aria-expanded="false">
                <span class="sr-only">Open menu</span>
                <span class="h-0.5 w-[18px] bg-ink"></span>
                <span class="h-0.5 w-[18px] bg-ink"></span>
                <span class="h-0.5 w-[18px] bg-ink"></span>
            </button>
        </div>
    </div>

    {{-- Slide-over drawer (not drawn in the handoff; built from the same tokens). --}}
    <div x-cloak x-show="open" class="fixed inset-0 z-50 xl:hidden" role="dialog" aria-modal="true" aria-labelledby="mobile-menu-title" id="mobile-menu"
         x-trap.inert.noscroll="open" @keydown.escape.window="close()">
        <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-[rgba(9,20,13,.6)]" @click="close()"></div>
        <div x-show="open"
             x-transition:enter="transition duration-250 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 flex w-full max-w-[380px] flex-col overflow-y-auto bg-surface shadow-lg">
            <div class="flex items-center justify-between border-b border-line px-5 py-3">
                <h2 id="mobile-menu-title" class="sr-only">Menu</h2>
                <img src="{{ Vite::asset('resources/images/ht-logo.png') }}" alt="" width="110" height="40" class="h-10 w-[110px] object-contain object-left mix-blend-multiply">
                <button type="button" @click="close()" class="flex size-11 cursor-pointer items-center justify-center rounded-ui border border-line text-ink hover:bg-bg">
                    <span class="sr-only">Close menu</span>
                    <x-ph name="x" class="size-5" />
                </button>
            </div>
            <nav aria-label="Main" class="px-5 py-2">
                <ul>
                    @foreach ($items as $item)
                        @php $active = Navigation::isActive($item['active']); @endphp
                        <li class="border-b border-line last:border-b-0">
                            <a href="{{ Navigation::href($item['route']) }}" @if ($active) aria-current="page" @endif
                               @class([
                                   'flex min-h-12 items-center justify-between py-3 text-base',
                                   'font-semibold text-g700' => $active,
                                   'font-medium text-ink hover:text-g700' => ! $active,
                               ])>
                                <span class="flex items-center gap-3">
                                    <span @class(['h-5 w-[3px] rounded-full', 'bg-g500' => $active, 'bg-transparent' => ! $active])></span>
                                    {{ $item['label'] }}
                                </span>
                                <x-ph name="caret-right" class="size-4 text-ink-3" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <div class="mt-auto space-y-3 border-t border-line bg-bg px-5 py-6">
                <a href="{{ route('quote') }}" class="btn btn-primary btn-lg w-full">Request a Quote</a>
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ $site->telHref($site->get('phone_primary')) }}" class="btn btn-outline w-full"><x-ph name="phone" class="size-4" /> Call</a>
                    <a href="{{ $site->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-whatsapp w-full"><x-ph name="whatsapp-logo" class="size-4" /> WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</header>
