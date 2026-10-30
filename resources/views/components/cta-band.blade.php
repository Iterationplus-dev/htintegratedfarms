@props(['title', 'href', 'label' => 'Request a Quote', 'tone' => 'dark', 'stacked' => false])
{{-- Closing call-to-action band. tone: dark (g900, white button) | sand (sand, primary button). --}}
<section @class(['py-[30px] md:py-[70px]', 'bg-g900' => $tone === 'dark', 'bg-sand' => $tone === 'sand'])>
    <div @class(['container-site', 'md:flex md:items-center md:gap-10' => ! $stacked])>
        <h2 @class([
            'text-[23px] md:text-[34px]',
            'text-white' => $tone === 'dark',
            'md:flex-1' => ! $stacked,
            'max-w-[640px] md:text-[36px]' => $stacked,
        ])>{{ $title }}</h2>
        <a href="{{ $href }}" @class([
            'btn btn-lg mt-[18px] w-full shrink-0 md:w-auto',
            'btn-light' => $tone === 'dark',
            'btn-primary' => $tone === 'sand',
            'md:mt-0' => ! $stacked,
            'md:mt-6' => $stacked,
        ])>{{ $label }}</a>
    </div>
</section>
