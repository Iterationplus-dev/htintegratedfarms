{{-- Cookie consent banner + preferences dialog (handoff §Cookie consent). State lives in Alpine.store('consent'). --}}
<div x-data>
    <section x-cloak x-show="!$store.consent.decided && !$store.consent.preferencesOpen"
             x-transition.opacity.duration.200ms
             aria-labelledby="cookie-banner-title" aria-describedby="cookie-banner-text"
             class="fixed inset-x-3 bottom-3 z-40 rounded-card border border-line bg-surface p-5 shadow-lg md:inset-x-6 md:bottom-6 md:flex md:items-center md:gap-8 md:px-7 md:py-6">
        <div class="min-w-0 flex-1">
            <h2 id="cookie-banner-title" class="text-base font-bold text-ink">We value your privacy</h2>
            <p id="cookie-banner-text" class="mt-1.5 max-w-[760px] text-sm leading-[1.6] text-ink-2">
                We use essential cookies to run this website. With your permission, we also use analytics cookies to understand how visitors use it. You can accept all, reject non-essential cookies, or choose your settings. See our <a href="{{ route('cookies') }}" class="text-g700 underline">Cookie Policy</a>.
            </p>
        </div>
        {{-- Mobile: two-up buttons + full-width "Manage preferences" --}}
        <div class="mt-4 md:hidden">
            <div class="grid grid-cols-2 gap-2.5">
                <button type="button" class="btn btn-outline" @click="$store.consent.rejectNonEssential()">Reject all</button>
                <button type="button" class="btn btn-primary" @click="$store.consent.acceptAll()">Accept all</button>
            </div>
            <button type="button" class="mt-1.5 min-h-11 w-full cursor-pointer text-sm font-semibold text-g700 underline" @click="$store.consent.openPreferences()">Manage preferences</button>
        </div>
        {{-- Desktop --}}
        <div class="hidden shrink-0 items-center gap-3 md:flex">
            <button type="button" class="min-h-11 cursor-pointer px-2 py-3 text-sm font-semibold text-g700 underline" @click="$store.consent.openPreferences()">Manage preferences</button>
            <button type="button" class="btn btn-outline" @click="$store.consent.rejectNonEssential()">Reject non-essential</button>
            <button type="button" class="btn btn-primary" @click="$store.consent.acceptAll()">Accept all</button>
        </div>
    </section>

    {{-- Preferences dialog (not drawn in the handoff; dialog pattern per README). --}}
    <div x-cloak x-show="$store.consent.preferencesOpen" class="fixed inset-0 z-50 flex items-end justify-center p-3 md:items-center md:p-6"
         role="dialog" aria-modal="true" aria-labelledby="cookie-prefs-title"
         x-trap.inert.noscroll="$store.consent.preferencesOpen" @keydown.escape.window="$store.consent.closePreferences()">
        <div class="absolute inset-0 bg-[rgba(9,20,13,.6)]" x-show="$store.consent.preferencesOpen" x-transition.opacity @click="$store.consent.closePreferences()"></div>
        <div class="relative w-full max-w-[560px] rounded-card border border-line bg-surface p-6 shadow-lg md:p-8" x-show="$store.consent.preferencesOpen" x-transition>
            <div class="flex items-start justify-between gap-4">
                <h2 id="cookie-prefs-title" class="text-xl font-bold">Cookie preferences</h2>
                <button type="button" class="-mt-2 -mr-2 flex size-11 cursor-pointer items-center justify-center rounded-ui text-ink-2 hover:bg-bg" @click="$store.consent.closePreferences()">
                    <span class="sr-only">Close</span><x-ph name="x" class="size-5" />
                </button>
            </div>
            <p class="mt-2 text-sm leading-[1.6] text-ink-2">Choose which cookies we may use. You can change this at any time from “Cookie settings” in the footer.</p>

            <div class="mt-6 divide-y divide-line rounded-ui border border-line">
                <div class="flex items-start justify-between gap-6 p-4">
                    <div>
                        <p class="text-[15px] font-bold" id="consent-essential-label">Essential</p>
                        <p class="mt-1 text-[13px] leading-[1.6] text-ink-2">Security, form protection and remembering this choice. Always on.</p>
                    </div>
                    <span role="switch" aria-checked="true" aria-disabled="true" aria-labelledby="consent-essential-label" class="relative mt-1 inline-flex h-6 w-11 shrink-0 cursor-not-allowed rounded-full bg-g700/60">
                        <span class="absolute top-0.5 right-0.5 size-5 rounded-full bg-white shadow-sm"></span>
                    </span>
                </div>
                <div class="flex items-start justify-between gap-6 p-4">
                    <div>
                        <p class="text-[15px] font-bold" id="consent-analytics-label">Analytics</p>
                        <p class="mt-1 text-[13px] leading-[1.6] text-ink-2">Anonymous statistics about which pages are visited, so we can improve the site.</p>
                    </div>
                    <button type="button" role="switch" aria-labelledby="consent-analytics-label"
                            :aria-checked="$store.consent.draftAnalytics.toString()" aria-checked="false"
                            @click="$store.consent.draftAnalytics = !$store.consent.draftAnalytics"
                            :class="$store.consent.draftAnalytics ? 'bg-g700' : 'bg-input'"
                            class="relative mt-1 inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full bg-input transition-colors">
                        <span :class="$store.consent.draftAnalytics ? 'translate-x-5' : 'translate-x-0'" class="absolute top-0.5 left-0.5 size-5 rounded-full bg-white shadow-sm transition-transform"></span>
                    </button>
                </div>
            </div>

            <div class="mt-6 grid gap-2.5 md:flex md:justify-end">
                <button type="button" class="btn btn-outline" @click="$store.consent.rejectNonEssential(); $store.consent.closePreferences()">Reject non-essential</button>
                <button type="button" class="btn btn-primary" @click="$store.consent.savePreferences()">Save preferences</button>
            </div>
        </div>
    </div>
</div>
