{{-- Form-level failure (rate limit, timing check, delivery failure) and a pointer to inline field errors. --}}
@error('form')
    <div {{ $attributes->class('mb-5 flex gap-3 rounded-ui border border-danger/30 bg-danger-bg px-4 py-3.5 text-sm text-danger') }} role="alert">
        <x-ph name="warning-circle" class="mt-px size-5" />
        <p>{{ $message }}</p>
    </div>
@else
    @if ($errors->isNotEmpty())
        <div {{ $attributes->class('mb-5 flex gap-3 rounded-ui border border-danger/30 bg-danger-bg px-4 py-3.5 text-sm text-danger') }} role="alert">
            <x-ph name="warning-circle" class="mt-px size-5" />
            <p>Please correct the {{ $errors->count() === 1 ? 'highlighted field' : $errors->count().' highlighted fields' }} below.</p>
        </div>
    @endif
@enderror
