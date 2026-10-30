<x-layouts.app title="Agricultural Products" description="Table eggs, broilers, point-of-lay birds, cattle, palm oil, fresh fruit bunches and palm kernels from H & T Integrated Farms. Request a quote for pricing and availability.">
    <section class="bg-bg">
        <div class="container-site pt-[26px] pb-[18px] md:pt-[52px] md:pb-[38px]">
            <x-breadcrumb :items="[['Products', null]]" class="mb-4 hidden md:block" />
            <h1 class="text-[31px] md:text-[46px]">Agricultural Products</h1>
            <p class="mt-2.5 max-w-[640px] text-[15px] leading-[1.7] text-ink-2 md:mt-3 md:text-[17px]">
                <span class="md:hidden">Inquiry-led supply for consumers, commercial buyers and distributors.</span>
                <span class="hidden md:inline">Livestock, farm produce and palm products supplied to consumers, commercial buyers and distributors. Submit an inquiry and we respond with pricing and availability.</span>
            </p>
        </div>
    </section>
    <livewire:product-index />
</x-layouts.app>
