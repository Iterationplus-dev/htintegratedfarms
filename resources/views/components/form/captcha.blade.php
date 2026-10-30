@php $captcha = app(\App\Contracts\CaptchaVerifier::class); @endphp
@if ($captcha->enabled())
    <div {{ $attributes->class('mt-5') }}>
        <div wire:ignore x-data="turnstile(@js($captcha->siteKey()))"></div>
        @error('captchaToken')
            <p class="field-error" role="alert">{{ $message }}</p>
        @enderror
    </div>
@endif
