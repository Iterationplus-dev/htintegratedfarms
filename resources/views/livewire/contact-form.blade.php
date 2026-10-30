<div>
    @if ($submitted)
        <x-form.success title="Thank you — your message has been sent" :reference="$reference">
            We have emailed you a confirmation. A member of the team will reply shortly, usually within one working day.
            For anything urgent call <a href="{{ $site->telHref($site->get('phone_primary')) }}" class="font-semibold">{{ $site->get('phone_primary') }}</a>.
        </x-form.success>
    @else
        <form wire:submit="submit" novalidate class="relative md:rounded-card md:border md:border-line md:p-[34px] md:shadow-sm" aria-labelledby="contact-form-title">
            <h2 id="contact-form-title" class="text-[23px] md:text-2xl">Send a message</h2>

            <x-form.error-summary class="mt-5" />

            <div class="mt-4 grid gap-3.5 md:mt-[22px] md:grid-cols-2 md:gap-[18px]">
                <x-form.field label="Full name" name="name" required>
                    <x-form.input name="name" wire:model.blur="name" autocomplete="name" placeholder="Your name" required />
                </x-form.field>
                <x-form.field label="Organisation" name="organisation">
                    <x-form.input name="organisation" wire:model.blur="organisation" autocomplete="organization" placeholder="Optional" />
                </x-form.field>
                <x-form.field label="Phone" name="phone" required>
                    <x-form.input name="phone" type="tel" wire:model.blur="phone" autocomplete="tel" inputmode="tel" placeholder="+234" required />
                </x-form.field>
                <x-form.field label="Email" name="email" required>
                    <x-form.input name="email" type="email" wire:model.blur="email" autocomplete="email" placeholder="name@company.com" required />
                </x-form.field>
            </div>

            {{-- Area of interest: single-select chips (md+) / select (mobile), same property. --}}
            <fieldset class="mt-3.5 hidden md:mt-5 md:block" @error('area') aria-describedby="area-error" @enderror>
                <legend class="field-label">Area of interest<span aria-hidden="true"> *</span><span class="sr-only"> (required)</span></legend>
                <div class="mt-2.5 flex flex-wrap gap-2" role="radiogroup">
                    @foreach ($areas as $option)
                        <label class="chip has-checked:border-g700 has-checked:bg-g700 has-checked:text-white has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-g500 font-medium">
                            <input type="radio" name="area" value="{{ $option->value }}" wire:model="area" class="sr-only">
                            {{ $option->getLabel() }}
                        </label>
                    @endforeach
                </div>
                @error('area')<p id="area-error" class="field-error" role="alert">{{ $message }}</p>@enderror
            </fieldset>
            <x-form.field label="Area of interest" name="area" required class="mt-3.5 md:hidden">
                <x-form.select name="area" wire:model="area" placeholder="Select" required>
                    @foreach ($areas as $option)
                        <option value="{{ $option->value }}">{{ $option->getLabel() }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field label="Message" name="message" required class="mt-3.5 md:mt-5">
                <x-form.textarea name="message" wire:model.blur="message" placeholder="How can we help?" class="md:h-[130px]" required />
            </x-form.field>

            <x-form.honeypot id="contact" />
            <x-form.captcha />

            <button type="submit" class="btn btn-primary btn-lg mt-[18px] w-full md:mt-[22px]" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Send Message</span>
                <span wire:loading wire:target="submit">Sending…</span>
            </button>
            <p class="mt-3 text-[13px] text-ink-3">Required fields marked *. We use your details only to reply to this message — see our <a href="{{ route('privacy') }}" class="underline">Privacy Policy</a>.</p>
        </form>
    @endif
</div>
