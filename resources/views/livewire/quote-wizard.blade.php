@php
    $products = $this->products;
    $completed = $this->completedSteps;
    $desktopCurrent = $this->firstIncompleteStep;
    $today = now()->toDateString();
@endphp
<div>
    {{-- Title band + step indicators --}}
    <section class="bg-bg">
        <div class="container-site pt-[26px] pb-[18px] md:pt-14 md:pb-[34px]">
            <h1 class="text-[31px] md:text-[46px]">Request a Quote</h1>
            <p class="mt-3 hidden max-w-[620px] text-[17px] leading-[1.7] text-ink-2 md:block">Tell us what you need and where it is going. We reply with pricing, availability and a delivery window.</p>

            @unless ($submitted)
                {{-- Desktop indicator: progress by completeness (all sections are visible). --}}
                <ol class="mt-[34px] hidden max-w-[720px] items-center xl:flex" aria-label="Progress">
                    @foreach ($steps as $number => $label)
                        @php $state = $completed[$number] ? 'done' : ($number === $desktopCurrent ? 'current' : 'upcoming'); @endphp
                        <li class="flex items-center gap-2.5" @if ($state === 'current') aria-current="step" @endif>
                            <span @class([
                                'flex size-8 items-center justify-center rounded-full text-[13px] font-bold',
                                'bg-g700 text-white' => $state !== 'upcoming',
                                'border border-step-line text-ink-3' => $state === 'upcoming',
                            ])>
                                @if ($state === 'done')<x-ph name="check" class="size-4" /><span class="sr-only">Completed:</span>@else{{ $number }}@endif
                            </span>
                            <span @class(['text-sm font-semibold', 'text-ink' => $state !== 'upcoming', 'text-ink-3' => $state === 'upcoming'])>{{ $label }}</span>
                        </li>
                        @unless ($loop->last)
                            <li aria-hidden="true" class="mx-4 h-px flex-1 bg-step-line"></li>
                        @endunless
                    @endforeach
                </ol>

                {{-- Mobile indicator: the wizard step. --}}
                <div class="mt-5 xl:hidden">
                    <ol class="flex items-center gap-2" aria-label="Progress">
                        @foreach ($steps as $number => $label)
                            <li @if ($number === $step) aria-current="step" @endif>
                                @if ($number < $step)
                                    <button type="button" wire:click="goToStep({{ $number }})" class="flex size-7 cursor-pointer items-center justify-center rounded-full bg-g700 text-white" aria-label="Back to step {{ $number }}: {{ $label }}">
                                        <x-ph name="check" class="size-3.5" />
                                    </button>
                                @else
                                    <span @class([
                                        'flex size-7 items-center justify-center rounded-full text-xs font-bold',
                                        'bg-g700 text-white' => $number === $step,
                                        'border border-step-line text-ink-3' => $number > $step,
                                    ])>{{ $number }}</span>
                                @endif
                            </li>
                            @unless ($loop->last)
                                <li aria-hidden="true" class="h-px flex-1 bg-step-line"></li>
                            @endunless
                        @endforeach
                    </ol>
                    <p class="mt-2.5 text-[13px] font-semibold text-ink-2">Step {{ $step }} of {{ count($steps) }} — {{ $steps[$step] }}</p>
                </div>
            @endunless
        </div>
    </section>

    <div class="container-site pt-[26px] pb-[34px] md:pt-14 md:pb-[90px]">
        @if ($submitted)
            <x-form.success title="Thank you — your quote request has been sent" :reference="$reference" again="Start another request" class="max-w-[720px]">
                Our sales team prepares quotes by email, typically within one working day. We have sent a copy of your request to your inbox.
            </x-form.success>
        @else
            <div class="grid items-start gap-6 xl:grid-cols-[1.5fr_1fr] xl:gap-14">
                <form id="quote-form" wire:submit="submit" novalidate class="relative min-w-0">
                    <x-form.error-summary />

                    {{-- 1 · Products --}}
                    <section @class(['xl:block', 'hidden' => $step !== 1]) aria-labelledby="quote-step-1">
                        <h2 id="quote-step-1" class="hidden text-[26px] xl:block">1 · What do you need?</h2>
                        <h2 class="sr-only xl:hidden">What do you need?</h2>
                        <div class="flex flex-col gap-3.5 xl:mt-[22px]">
                            @foreach ($items as $index => $item)
                                @php $product = $item['product_id'] ? $products->get((int) $item['product_id']) : null; @endphp
                                <div wire:key="quote-item-{{ $index }}" @class([
                                    'rounded-card border p-4 md:p-5',
                                    'border-g500 bg-g50' => $product,
                                    'border-line' => ! $product,
                                ])>
                                    <div class="grid gap-3 md:grid-cols-[52px_minmax(0,1fr)_170px_150px_auto] md:items-start md:gap-[18px]">
                                        <div class="hidden size-[52px] overflow-hidden rounded-ui md:block">
                                            <x-picture :media="$product?->cover()" :alt="''" sizes="52px" placeholder="" class="h-full" />
                                        </div>
                                        <div class="min-w-0 md:self-center">
                                            @if ($product)
                                                <p class="text-base font-bold">{{ $product->name }}</p>
                                                <p class="mt-[3px] text-[13px] text-ink-2">{{ $product->categoryName() }} · {{ $product->availability->getLabel() }}</p>
                                            @else
                                                <x-form.field label="Product" :name="'items.'.$index.'.product_id'" required label-class="field-label text-[10px]">
                                                    <x-form.select :name="'items.'.$index.'.product_id'" wire:model.live="items.{{ $index }}.product_id" placeholder="Choose a product" class="bg-surface">
                                                        @foreach ($products->groupBy(fn ($p) => $p->categoryName()) as $category => $group)
                                                            <optgroup label="{{ $category }}">
                                                                @foreach ($group as $option)
                                                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </x-form.select>
                                                </x-form.field>
                                            @endif
                                        </div>
                                        <div class="grid grid-cols-2 gap-2.5 md:contents">
                                            <x-form.field label="Quantity" :name="'items.'.$index.'.quantity'" required label-class="field-label text-[10px]">
                                                <div class="relative">
                                                    <x-form.input :name="'items.'.$index.'.quantity'" type="number" min="1" step="1" inputmode="numeric"
                                                                  wire:model.live.blur="items.{{ $index }}.quantity" placeholder="Quantity" required
                                                                  class="mt-[5px] bg-surface py-[11px] pr-16 font-medium" />
                                                    @if ($product?->unit)
                                                        <span class="pointer-events-none absolute top-1/2 right-3 mt-[2px] -translate-y-1/2 text-sm text-ink-3">{{ $product->unit }}</span>
                                                    @endif
                                                </div>
                                            </x-form.field>
                                            <x-form.field label="Frequency" :name="'items.'.$index.'.frequency'" required label-class="field-label text-[10px]">
                                                <x-form.select :name="'items.'.$index.'.frequency'" wire:model.live="items.{{ $index }}.frequency" placeholder="Frequency" class="mt-[5px] bg-surface py-[11px] font-medium">
                                                    @foreach ($frequencies as $frequency)
                                                        <option value="{{ $frequency->value }}">{{ $frequency->getLabel() }}</option>
                                                    @endforeach
                                                </x-form.select>
                                            </x-form.field>
                                        </div>
                                        @if (count($items) > 1)
                                            <button type="button" wire:click="removeItem({{ $index }})" class="flex size-11 cursor-pointer items-center justify-center justify-self-end rounded-ui text-ink-3 hover:bg-surface hover:text-danger md:mt-[18px]">
                                                <x-ph name="trash" class="size-5" /><span class="sr-only">Remove {{ $product?->name ?? 'this product' }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if (count($items) < \App\Livewire\QuoteWizard::MAX_ITEMS)
                                <button type="button" wire:click="addItem" class="btn btn-lg w-full border-dashed border-g500 bg-transparent text-sm text-g700 hover:bg-g50">
                                    <x-ph name="plus" class="size-4" /> Add another product
                                </button>
                            @endif
                            @if ($products->isEmpty())
                                <p class="rounded-ui bg-sand p-4 text-sm text-ink-2">No products are open for quotes right now. Please <a href="{{ route('contact') }}" class="font-semibold">contact us</a> directly.</p>
                            @endif
                        </div>
                    </section>

                    {{-- 2 · Delivery --}}
                    <section @class(['xl:mt-11 xl:block', 'hidden' => $step !== 2]) aria-labelledby="quote-step-2">
                        <h2 id="quote-step-2" class="text-[22px] xl:text-[26px]">2 · Delivery</h2>
                        <div class="mt-4 grid gap-3.5 md:grid-cols-2 md:gap-[18px] xl:mt-[22px]">
                            <x-form.field label="Preferred delivery location" name="deliveryLocation" required>
                                <x-form.input name="deliveryLocation" wire:model.live.blur="deliveryLocation" autocomplete="address-level2" placeholder="City, state" required />
                            </x-form.field>
                            <x-form.field label="Required by" name="requiredBy" required>
                                <x-form.input name="requiredBy" type="date" min="{{ $today }}" wire:model.live.blur="requiredBy" required />
                            </x-form.field>
                        </div>
                    </section>

                    {{-- 3 · Your details --}}
                    <section @class(['xl:mt-11 xl:block', 'hidden' => $step !== 3]) aria-labelledby="quote-step-3">
                        <h2 id="quote-step-3" class="text-[22px] xl:text-[26px]">3 · Your details</h2>
                        <div class="mt-4 grid gap-3.5 md:grid-cols-2 md:gap-[18px] xl:mt-[22px]">
                            <x-form.field label="Full name" name="name" required>
                                <x-form.input name="name" wire:model.live.blur="name" autocomplete="name" placeholder="Your name" required />
                            </x-form.field>
                            <x-form.field label="Organisation" name="organisation">
                                <x-form.input name="organisation" wire:model.live.blur="organisation" autocomplete="organization" placeholder="Optional" />
                            </x-form.field>
                            <x-form.field label="Phone" name="phone" required>
                                <x-form.input name="phone" type="tel" wire:model.live.blur="phone" autocomplete="tel" inputmode="tel" placeholder="+234" required />
                            </x-form.field>
                            <x-form.field label="Email" name="email" required>
                                <x-form.input name="email" type="email" wire:model.live.blur="email" autocomplete="email" placeholder="name@company.com" required />
                            </x-form.field>
                        </div>
                        <x-form.captcha />
                    </section>

                    <x-form.honeypot id="quote" />

                    {{-- Mobile wizard controls --}}
                    <div class="mt-[22px] flex flex-col gap-2.5 xl:hidden">
                        @if ($step < count($steps))
                            <button type="button" wire:click="next" class="btn btn-primary btn-lg w-full" wire:loading.attr="disabled" wire:target="next">Continue</button>
                        @else
                            <button type="submit" class="btn btn-primary btn-lg w-full" wire:loading.attr="disabled" wire:target="submit">
                                <span wire:loading.remove wire:target="submit">Submit Quote Request</span>
                                <span wire:loading wire:target="submit">Sending…</span>
                            </button>
                        @endif
                        @if ($step > 1)
                            <button type="button" wire:click="back" class="link-arrow justify-center"><x-ph name="arrow-left" class="size-4" /> Back</button>
                        @endif
                    </div>
                </form>

                {{-- Summary (desktop) + save for later (all sizes) --}}
                <aside class="rounded-card xl:sticky xl:top-5 xl:border xl:border-line xl:bg-bg xl:p-7" aria-label="Your request">
                    <div class="hidden xl:block">
                        <p class="text-[11px] font-semibold tracking-[0.12em] text-ink-3 uppercase">Your request</p>
                        <ul class="mt-[18px] flex flex-col gap-3.5">
                            @forelse ($this->summary as $line)
                                <li @class(['flex justify-between gap-4 text-[15px]', 'text-ink-3' => $line['pending']])>
                                    <span>{{ $line['name'] }}</span>
                                    <span @class(['text-right', 'font-semibold' => ! $line['pending']])>{{ $line['detail'] }}</span>
                                </li>
                            @empty
                                <li class="text-[15px] text-ink-3">No products chosen yet.</li>
                            @endforelse
                        </ul>
                        <p class="mt-5 border-t border-line pt-[18px] text-sm leading-[1.8] text-ink-2">Quotes are prepared by the sales team and sent by email. Typical response within one working day.</p>
                        <button type="submit" form="quote-form" class="btn btn-primary btn-lg mt-5 w-full" wire:loading.attr="disabled" wire:target="submit">
                            <span wire:loading.remove wire:target="submit">Submit Quote Request</span>
                            <span wire:loading wire:target="submit">Sending…</span>
                        </button>
                    </div>

                    <div x-data="{ open: @js($draftSavedTo !== null || $errors->has('draftEmail')) }" class="xl:mt-2.5">
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="save-draft-panel" class="btn btn-outline btn-lg w-full">Save and continue later</button>
                        <div id="save-draft-panel" x-cloak x-show="open" x-collapse>
                            <div class="mt-3 rounded-ui border border-line bg-surface p-4">
                                @if ($draftSavedTo)
                                    <p class="flex gap-2.5 text-sm leading-[1.6] text-ink-2" role="status">
                                        <x-ph name="envelope-simple" class="mt-0.5 size-5 text-g700" />
                                        <span>We’ve emailed a link to <strong class="text-ink">{{ $draftSavedTo }}</strong>. It keeps your request for {{ config('enquiries.draft_lifetime_days') }} days. Nothing has been sent to our sales team yet.</span>
                                    </p>
                                @else
                                    <x-form.field label="Email the link to" name="draftEmail" required>
                                        <x-form.input name="draftEmail" type="email" wire:model="draftEmail" autocomplete="email" placeholder="name@company.com" />
                                    </x-form.field>
                                    <button type="button" wire:click="saveDraft" class="btn btn-primary mt-3 w-full" wire:loading.attr="disabled" wire:target="saveDraft">
                                        <span wire:loading.remove wire:target="saveDraft">Email me a link</span>
                                        <span wire:loading wire:target="saveDraft">Saving…</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</div>
