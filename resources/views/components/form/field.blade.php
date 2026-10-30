@props(['label', 'name', 'required' => false, 'hint' => null, 'labelClass' => 'field-label'])
@php $id = str_replace('.', '-', $name); @endphp
<div {{ $attributes }}>
    <label for="{{ $id }}" class="{{ $labelClass }}">
        {{ $label }}@if ($required)<span aria-hidden="true"> *</span><span class="sr-only"> (required)</span>@endif
    </label>
    {{ $slot }}
    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-[13px] text-ink-3">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $id }}-error" class="field-error" role="alert">{{ $message }}</p>
    @enderror
</div>
