@props(['title', 'accent' => 'g500'])
@php
    $border = ['g500' => 'border-g500', 'sky' => 'border-sky', 'leaf' => 'border-leaf'][$accent] ?? 'border-g500';
@endphp
<div {{ $attributes->class("border-l-2 {$border} pl-4") }}>
    <p class="text-[15px] font-bold">{{ $title }}</p>
    <p class="mt-1 text-sm leading-[1.7] text-ink-2">{{ $slot }}</p>
</div>
