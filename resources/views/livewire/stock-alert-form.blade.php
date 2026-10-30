<div>
    @if ($subscribed)
        <div role="status" class="pr-8">
            <span class="flex size-10 items-center justify-center rounded-full bg-g700 text-white"><x-ph name="check" class="size-5" /></span>
            <h2 class="mt-4 text-xl">You’re on the list</h2>
            <p class="mt-2 text-sm leading-[1.6] text-ink-2">We’ll email you once when {{ $productName }} is available again. You can then request a quote.</p>
        </div>
    @else
        <form wire:submit="subscribe" novalidate class="relative">
            <h2 class="pr-8 text-xl">Notify me about {{ $productName }}</h2>
            <p class="mt-2 text-sm leading-[1.6] text-ink-2">This product is out of season. Leave your email and we’ll send one message when it can be ordered again.</p>
            <x-form.field label="Email" name="email" required class="mt-5">
                <x-form.input name="email" type="email" wire:model="email" autocomplete="email" placeholder="name@company.com" required />
            </x-form.field>
            <x-form.honeypot id="stock-{{ $productId }}" />
            <button type="submit" class="btn btn-primary btn-lg mt-5 w-full" wire:loading.attr="disabled" wire:target="subscribe">
                <span wire:loading.remove wire:target="subscribe">Notify me</span>
                <span wire:loading wire:target="subscribe">Saving…</span>
            </button>
        </form>
    @endif
</div>
