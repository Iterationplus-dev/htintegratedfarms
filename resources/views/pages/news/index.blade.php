{{-- News list: not designed in the handoff — built with the homepage "Latest Farm Updates" cards. --}}
<x-layouts.app title="Farm Updates" description="Insights from H & T Integrated Farms on poultry, livestock health and oil palm production.">
    <section class="bg-bg">
        <div class="container-site pt-[26px] pb-[34px] md:pt-[52px] md:pb-[90px]">
            <x-breadcrumb :items="[['News', null]]" class="mb-4 hidden md:block" />
            <h1 class="text-[31px] md:text-[46px]">Latest Farm Updates</h1>
            <p class="mt-3 max-w-[620px] text-[15px] leading-[1.7] text-ink-2 md:text-[17px]">Notes from the farm on how we manage flocks, herds and the plantation.</p>

            @if ($posts->isEmpty())
                <p class="mt-10 rounded-card border border-dashed border-input bg-surface px-6 py-14 text-center text-ink-2">No updates have been published yet.</p>
            @else
                <div class="mt-8 grid gap-4 md:mt-11 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-post-card :post="$post" />
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.app>
