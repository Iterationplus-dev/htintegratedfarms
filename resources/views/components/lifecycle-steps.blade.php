@props(['steps', 'withDescriptions' => true])
{{--
    Six-step livestock lifecycle (handoff §01.7 / §03). Desktop: numbered strip with connecting rule.
    Mobile: vertical list. The final step uses navy to mark the hand-off to buyers.
    $steps: list of [title, description].
--}}
<div {{ $attributes->class('relative') }}>
    <div aria-hidden="true" class="absolute top-[19px] right-0 left-0 hidden h-px bg-[linear-gradient(90deg,transparent,#C6CFC4_48px,#C6CFC4_calc(100%-48px),transparent)] xl:block"></div>
    <ol class="flex flex-col md:grid md:grid-cols-3 md:gap-x-5 md:gap-y-10 xl:grid-cols-6">
        @foreach ($steps as $index => [$title, $description])
            <li class="relative flex items-center gap-3.5 border-b border-[#DDE2D9] py-3 last:border-b-0 md:block md:border-0 md:py-0">
                <span @class([
                    'flex size-[30px] shrink-0 items-center justify-center rounded-full text-[13px] font-bold text-white md:size-[38px] md:text-sm',
                    'bg-navy' => $loop->last,
                    'bg-g700' => ! $loop->last,
                ])>{{ $index + 1 }}</span>
                <div class="md:mt-[18px]">
                    <p class="text-base font-bold md:text-[17px]">{{ $title }}</p>
                    @if ($withDescriptions && $description)
                        <p class="mt-1.5 hidden text-sm leading-[1.6] text-ink-2 md:block">{{ $description }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</div>
