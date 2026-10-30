@props(['items' => [], 'dark' => false])
{{-- $items: list of [label, url|null]; the last item is the current page. --}}
<nav aria-label="Breadcrumb" {{ $attributes->class(['text-xs md:text-[13px]', 'text-on-dark-accent' => $dark, 'text-ink-3' => ! $dark]) }}>
    <ol class="flex flex-wrap items-center gap-1">
        <li><a href="{{ route('home') }}" @class(['hover:underline', 'text-on-dark-accent hover:text-white' => $dark, 'text-ink-3 hover:text-g700' => ! $dark])>Home</a></li>
        @foreach ($items as [$label, $url])
            <li aria-hidden="true" @class(['text-[#C0C9BE]' => ! $dark])>/</li>
            <li>
                @if ($loop->last || ! $url)
                    <span aria-current="page" @class(['text-ink' => ! $dark])>{{ $label }}</span>
                @else
                    <a href="{{ $url }}" @class(['hover:underline', 'text-on-dark-accent hover:text-white' => $dark, 'text-ink-3 hover:text-g700' => ! $dark])>{{ $label }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
