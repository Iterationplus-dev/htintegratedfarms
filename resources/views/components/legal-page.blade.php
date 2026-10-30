@props(['title', 'updated', 'description'])
<x-layouts.app :title="$title" :description="$description">
    <section class="bg-bg">
        <div class="container-site pt-[26px] pb-8 md:pt-[52px] md:pb-12">
            <x-breadcrumb :items="[[$title, null]]" class="mb-4 hidden md:block" />
            <h1 class="text-[31px] md:text-[46px]">{{ $title }}</h1>
            <p class="mt-3 text-sm text-ink-3">Last updated {{ $updated }}</p>
        </div>
    </section>
    <div class="container-site py-10 md:py-16">
        <div class="prose-ht max-w-[760px] [&>h2:first-child]:mt-0">{{ $slot }}</div>
    </div>
</x-layouts.app>
