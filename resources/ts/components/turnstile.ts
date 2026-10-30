import type { AlpineComponent } from 'alpinejs';

interface TurnstileApi {
    render(container: HTMLElement, options: Record<string, unknown>): string;
    reset(widgetId?: string): void;
    remove(widgetId: string): void;
}

declare global {
    interface Window {
        turnstile?: TurnstileApi;
    }
}

interface LivewireWire {
    $set(name: string, value: unknown, live?: boolean): void;
}

interface Turnstile {
    siteKey: string;
    property: string;
    widgetId: string | null;
    onReset: (() => void) | null;
}

const SCRIPT_SRC = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
let scriptPromise: Promise<TurnstileApi> | null = null;

function loadTurnstile(): Promise<TurnstileApi> {
    if (window.turnstile) {
        return Promise.resolve(window.turnstile);
    }
    scriptPromise ??= new Promise<TurnstileApi>((resolve, reject) => {
        const script = document.createElement('script');
        script.src = SCRIPT_SRC;
        script.async = true;
        script.onload = () => (window.turnstile ? resolve(window.turnstile) : reject(new Error('Turnstile unavailable')));
        script.onerror = () => {
            scriptPromise = null;
            reject(new Error('Turnstile failed to load'));
        };
        document.head.appendChild(script);
    });

    return scriptPromise;
}

/**
 * Cloudflare Turnstile widget bound to a Livewire property. Only rendered when TURNSTILE_SITE_KEY
 * is configured; the server verifies the token (App\Services\Captcha\TurnstileVerifier).
 * Place inside a `wire:ignore` element so Livewire morphing does not wipe the iframe.
 */
export function turnstile(siteKey: string, property = 'captchaToken'): AlpineComponent<Turnstile> {
    return {
        siteKey,
        property,
        widgetId: null,
        onReset: null,

        init(): void {
            const wire = (this as unknown as { $wire: LivewireWire }).$wire;
            void loadTurnstile()
                .then((api) => {
                    this.widgetId = api.render(this.$el, {
                        sitekey: this.siteKey,
                        theme: 'light',
                        callback: (token: string) => wire.$set(this.property, token, false),
                        'expired-callback': () => wire.$set(this.property, '', false),
                        'error-callback': () => wire.$set(this.property, '', false),
                    });
                })
                .catch(() => {
                    // Server-side verification will reject the submission with a visible error.
                });

            this.onReset = () => {
                if (this.widgetId !== null) {
                    window.turnstile?.reset(this.widgetId);
                }
            };
            window.addEventListener('captcha-reset', this.onReset);
        },

        destroy(): void {
            if (this.onReset) {
                window.removeEventListener('captcha-reset', this.onReset);
            }
            if (this.widgetId !== null) {
                window.turnstile?.remove(this.widgetId);
            }
        },
    };
}
