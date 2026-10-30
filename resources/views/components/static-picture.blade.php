@props([
    'src',
    'alt' => '',
    'sizes' => '100vw',
    'loading' => 'lazy',
    'priority' => false,
    'imgClass' => 'h-full w-full object-cover',
    'position' => null,
])
{{-- Responsive <picture> for build-time imagery in resources/images (see resources/ts/app.ts glob). --}}
@php
    $srcset = collect([400, 800, 1600])->map(fn (int $w) => Vite::asset("resources/images/{$src}-{$w}.webp")." {$w}w")->implode(', ');
@endphp
<picture {{ $attributes->class('block') }}>
    <source type="image/webp" srcset="{{ $srcset }}" sizes="{{ $sizes }}">
    <img src="{{ Vite::asset("resources/images/{$src}.jpg") }}" alt="{{ $alt }}" class="{{ $imgClass }}"
         @if ($priority) fetchpriority="high" loading="eager" @else loading="{{ $loading }}" @endif
         decoding="async" @if ($position) style="object-position: {{ $position }}" @endif>
</picture>
