<div>
    @if ($submitted)
        <x-form.success title="Thank you — your inquiry has been received" :reference="$reference" again="Send another inquiry">
            Partnership inquiries are reviewed by the management team and answered directly. A confirmation has been sent to your email.
        </x-form.success>
    @else
        <form wire:submit="submit" novalidate class="relative md:rounded-card md:border md:border-line md:p-[34px] md:shadow-sm" aria-label="Become a Partner">
            <x-form.error-summary />

            <div class="grid gap-3.5 md:grid-cols-2 md:gap-[18px]">
                <x-form.field label="Organisation" name="organisation" required>
                    <x-form.input name="organisation" wire:model.blur="organisation" autocomplete="organization" placeholder="Company name" required />
                </x-form.field>
                <x-form.field label="Contact person" name="name" required>
                    <x-form.input name="name" wire:model.blur="name" autocomplete="name" placeholder="Full name" required />
                </x-form.field>
                <x-form.field label="Email" name="email" required>
                    <x-form.input name="email" type="email" wire:model.blur="email" autocomplete="email" placeholder="name@company.com" required />
                </x-form.field>
                <x-form.field label="Phone" name="phone">
                    <x-form.input name="phone" type="tel" wire:model.blur="phone" autocomplete="tel" inputmode="tel" placeholder="+234" />
                </x-form.field>
            </div>

            <x-form.field label="Partnership type" name="partnershipType" required class="mt-3.5 md:mt-[18px]">
                <x-form.select name="partnershipType" wire:model="partnershipType" placeholder="Select a category" required>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}">{{ $type->getLabel() }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>

            <x-form.field label="Proposal" name="proposal" required class="mt-3.5 md:mt-[18px]">
                <x-form.textarea name="proposal" wire:model.blur="proposal" placeholder="Describe what you are proposing…" class="h-[100px]" required />
            </x-form.field>

            <x-form.honeypot id="partner" />
            <x-form.captcha />

            <button type="submit" class="btn btn-primary btn-lg mt-[18px] w-full md:mt-[22px]" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit"><span class="md:hidden">Submit Inquiry</span><span class="hidden md:inline">Submit Partnership Inquiry</span></span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </button>
            <p class="mt-3 text-[13px] text-ink-3">Required fields marked *.</p>
        </form>
    @endif
</div>
