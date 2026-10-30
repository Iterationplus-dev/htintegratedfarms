@props(['code', 'title', 'message'])
<x-layouts.app :title="$title" noindex>
    <section class="bg-bg">
        <div class="container-site py-16 md:py-28">
            <p class="eyebrow">Error {{ $code }}</p>
            <h1 class="mt-4 max-w-[640px] text-[31px] md:text-[46px]">{{ $title }}</h1>
            <p class="mt-4 max-w-[560px] text-[17px] leading-[1.7] text-ink-2">{{ $message }}</p>
            <div class="mt-8 flex flex-col gap-3 md:flex-row">
                <a href="{{ route('home') }}" class="btn btn-primary btn-lg">Go to the homepage</a>
                <a href="{{ route('contact') }}" class="btn btn-outline btn-lg">Contact us</a>
            </div>
        </div>
    </section>
</x-layouts.app>
