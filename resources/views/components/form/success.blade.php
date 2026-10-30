@props(['title', 'reference' => null, 'again' => 'Send another message'])
{{-- Success state that replaces a submitted form (not drawn in the handoff; built from tokens). --}}
<div {{ $attributes->class('rounded-card border border-g500/40 bg-g50 p-6 md:p-8') }} role="status" tabindex="-1" data-success-focus>
    <span class="flex size-12 items-center justify-center rounded-full bg-g700 text-white">
        <x-ph name="check" class="size-6" />
    </span>
    <h2 class="mt-5 text-2xl">{{ $title }}</h2>
    <div class="mt-3 text-[15px] leading-[1.7] text-ink-2">{{ $slot }}</div>
    @if ($reference)
        <p class="mt-5 inline-flex items-center gap-2 rounded-ui bg-surface px-4 py-2.5 text-sm text-ink-2 shadow-sm">
            Your reference <strong class="font-bold tracking-[0.04em] text-ink">{{ $reference }}</strong>
        </p>
    @endif
    <div class="mt-6">
        <button type="button" wire:click="startAnother" class="btn btn-outline">{{ $again }}</button>
    </div>
</div>
