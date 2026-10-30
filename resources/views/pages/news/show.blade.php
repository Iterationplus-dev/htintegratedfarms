{{-- Article detail: not designed in the handoff — built from the same tokens. --}}
<x-layouts.app :title="$post->title" :description="$post->excerpt ?? $post->title" :og-image="$post->coverImage()?->getUrl('w1600')">
    <article>
        <header class="bg-bg">
            <div class="container-site pt-[26px] pb-8 md:pt-[52px] md:pb-12">
                <x-breadcrumb :items="[['News', route('news.index')], [$post->title, null]]" class="mb-5 hidden md:block" />
                <div class="max-w-[780px]">
                    <p class="text-[11px] font-medium tracking-[0.12em] text-g600 uppercase">{{ $post->eyebrow() }}</p>
                    <h1 class="mt-3 text-[31px] md:text-[46px]">{{ $post->title }}</h1>
                    @if ($post->excerpt)
                        <p class="mt-4 text-[17px] leading-[1.7] text-ink-2">{{ $post->excerpt }}</p>
                    @endif
                    <p class="mt-4 text-[13px] text-ink-3"><time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time>@if ($post->topic) · {{ $post->topic }}@endif</p>
                </div>
            </div>
        </header>
        @if ($post->coverImage())
            <div class="container-site -mb-4 pt-8 md:pt-12">
                <x-picture :media="$post->coverImage()" :alt="$post->title" sizes="(min-width: 1280px) 1200px, 100vw" priority class="h-[220px] overflow-hidden rounded-card md:h-[440px]" />
            </div>
        @endif
        <div class="container-site py-10 md:py-16">
            <div class="prose-ht max-w-[720px]">{!! $post->safeBody() !!}</div>
        </div>
    </article>

    @if ($more->isNotEmpty())
        <section class="border-t border-line bg-bg py-[34px] md:py-[70px]" aria-labelledby="more-updates">
            <div class="container-site">
                <div class="mb-6 flex items-end justify-between md:mb-8">
                    <h2 id="more-updates" class="text-2xl md:text-[28px]">More farm updates</h2>
                    <a href="{{ route('news.index') }}" class="link-arrow">All insights <span aria-hidden="true">→</span></a>
                </div>
                <div class="grid gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
                    @foreach ($more as $item)
                        <x-post-card :post="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
