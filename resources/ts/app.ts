/**
 * Public-site entry point. Livewire's ESM build bundles Alpine; we register our typed components
 * on that same Alpine instance and then start Livewire once, so Alpine is never initialised twice.
 * The layout uses @livewireScriptConfig (not @livewireScripts) for exactly this reason.
 */
import { Alpine, Livewire } from '@livewire';
import { heroSlider } from './components/heroSlider';
import { mobileNav } from './components/mobileNav';
import { createConsentStore } from './components/cookieConsent';
import { gallery } from './components/gallery';
import { turnstile } from './components/turnstile';

// Make static imagery referenced from Blade (Vite::asset) part of the manifest.
import.meta.glob(['../images/**'], { eager: true, query: '?url', import: 'default' });

Alpine.store('consent', createConsentStore());
Alpine.data('heroSlider', heroSlider);
Alpine.data('mobileNav', mobileNav);
Alpine.data('gallery', gallery);
Alpine.data('turnstile', turnstile);

// After a Livewire form swaps to its success state, move focus to the confirmation for screen readers.
window.addEventListener('enquiry-submitted', () => {
    window.requestAnimationFrame(() => document.querySelector<HTMLElement>('[data-success-focus]')?.focus());
});

Livewire.start();
