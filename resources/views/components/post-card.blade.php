@props(['post'])
<article {{ $attributes->class('card flex flex-col') }}>
    <a href="{{ route('news.show', $post) }}" class="block h-[180px] overflow-hidden" tabindex="-1" aria-hidden="true">
        <x-picture :media="$post->coverImage()" :alt="$post->title" sizes="(min-width: 1280px) 384px, (min-width: 768px) 45vw, 100vw" class="h-full" />
    </a>
    <div class="p-6">
        <p class="text-[11px] font-medium tracking-[0.12em] text-g600 uppercase">{{ $post->eyebrow() }}</p>
        <h3 class="mt-2.5 text-[19px]"><a href="{{ route('news.show', $post) }}" class="text-ink hover:text-g700">{{ $post->title }}</a></h3>
        <p class="mt-3 text-[13px] text-ink-3">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time>@if ($post->topic) · {{ $post->topic }}@endif
        </p>
    </div>
</article>
