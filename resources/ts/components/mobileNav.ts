import type { AlpineComponent } from 'alpinejs';

interface MobileNav {
    open: boolean;
    query: MediaQueryList | null;
    onChange: ((event: MediaQueryListEvent) => void) | null;
    toggle(): void;
    close(): void;
}

/**
 * Slide-over navigation below the 1280px breakpoint. Focus trapping and scroll locking are done
 * in markup with Alpine's focus plugin (`x-trap.inert.noscroll`), which ships inside Livewire's Alpine.
 */
export function mobileNav(): AlpineComponent<MobileNav> {
    return {
        open: false,
        query: null,
        onChange: null,

        init(): void {
            this.query = window.matchMedia('(min-width: 1280px)');
            this.onChange = (event: MediaQueryListEvent) => {
                if (event.matches) {
                    this.close();
                }
            };
            this.query.addEventListener('change', this.onChange);
        },

        destroy(): void {
            if (this.query && this.onChange) {
                this.query.removeEventListener('change', this.onChange);
            }
        },

        toggle(): void {
            this.open = !this.open;
        },

        close(): void {
            this.open = false;
        },
    };
}
