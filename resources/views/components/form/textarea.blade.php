@props(['name', 'required' => false, 'rows' => 5])
@php
    $id = str_replace('.', '-', $name);
    $invalid = $errors->has($name);
@endphp
<textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($required) aria-required="true" @endif
          @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
          {{ $attributes->class('field-input h-[110px] resize-y md:h-[120px]') }}></textarea>
