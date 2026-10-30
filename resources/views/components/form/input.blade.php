@props(['name', 'type' => 'text', 'required' => false])
@php
    $id = str_replace('.', '-', $name);
    $invalid = $errors->has($name);
@endphp
<input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @if ($required) aria-required="true" @endif
       @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
       {{ $attributes->class('field-input') }}>
