@props(['name'])
<span {{ $attributes->class('inline-flex shrink-0 [&>svg]:size-full') }}>{!! \App\Support\Icon::svg($name) !!}</span>
