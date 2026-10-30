@props(['name', 'required' => false, 'placeholder' => 'Select'])
@php
    $id = str_replace('.', '-', $name);
    $invalid = $errors->has($name);
@endphp
<div class="relative">
    <select id="{{ $id }}" name="{{ $name }}" @if ($required) aria-required="true" @endif
            @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->class('field-input cursor-pointer appearance-none pr-10') }}>
        <option value="">{{ $placeholder }}</option>
        {{ $slot }}
    </select>
    <x-ph name="caret-down" class="pointer-events-none absolute top-1/2 right-3.5 mt-[3px] size-4 -translate-y-1/2 text-ink-3" />
</div>
