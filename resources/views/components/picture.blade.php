@props([
    'media' => null,
    'alt' => '',
    'sizes' => '100vw',
    'loading' => 'lazy',
    'priority' => false,
    'imgClass' => 'h-full w-full object-cover',
    'position' => null,
    'placeholder' => 'Image coming soon',
])
{{--
    Responsive <picture> for a Media Library item: WebP srcset (400/800/1600) with a JPEG fallback.
    Falls back to the original file while queued conversions are still being generated.
--}}
@if ($media)
    @php
        $srcset = collect(\App\Models\Product::RESPONSIVE_WIDTHS)
            ->filter(fn (int $w) => $media->hasGeneratedConversion("w{$w}"))
            ->map(fn (int $w) => $media->getUrl("w{$w}")." {$w}w")
            ->implode(', ');
        $fallback = $media->hasGeneratedConversion('fallback') ? $media->getUrl('fallback') : $media->getUrl();
    @endphp
    <picture {{ $attributes->class('block') }}>
        @if ($srcset !== '')
            <source type="image/webp" srcset="{{ $srcset }}" sizes="{{ $sizes }}">
        @endif
        <img src="{{ $fallback }}" alt="{{ $alt }}" class="{{ $imgClass }}"
             @if ($priority) fetchpriority="high" loading="eager" @else loading="{{ $loading }}" @endif
             decoding="async" @if ($position) style="object-position: {{ $position }}" @endif>
    </picture>
@else
    <x-image-placeholder :label="$placeholder" {{ $attributes }} />
@endif
