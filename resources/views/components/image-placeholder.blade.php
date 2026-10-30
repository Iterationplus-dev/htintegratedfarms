@props(['label' => 'Image coming soon'])
{{-- Designed empty state for missing imagery (e.g. Palm Kernels): not drawn in the handoff, built from tokens. --}}
<div {{ $attributes->class('flex h-full w-full flex-col items-center justify-center gap-2 bg-sand text-ink-3') }} role="img" aria-label="{{ $label }}">
    <x-ph name="leaf" class="size-7 text-g500/60" />
    <span class="text-xs font-medium tracking-[0.06em]">{{ $label }}</span>
</div>
