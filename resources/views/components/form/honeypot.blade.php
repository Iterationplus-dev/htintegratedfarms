@props(['id' => 'website'])
{{-- Honeypot: invisible to people and assistive tech; bots that fill it are silently ignored. --}}
<div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
    <label for="{{ $id }}-hp">Leave this field empty</label>
    <input type="text" id="{{ $id }}-hp" wire:model="website" tabindex="-1" autocomplete="off">
</div>
