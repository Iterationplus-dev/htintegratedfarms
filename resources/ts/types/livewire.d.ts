/**
 * Typings for Livewire's ESM bundle (vendor/livewire/livewire/dist/livewire.esm.js),
 * aliased as "@livewire" in vite.config.js. Only the members this app uses are declared.
 */
declare module '@livewire' {
    import type { Alpine as AlpineInstance } from 'alpinejs';

    export interface LivewireHookPayload {
        component: { id: string; name: string };
        [key: string]: unknown;
    }

    export interface LivewireGlobal {
        start(): void;
        hook(name: string, callback: (payload: LivewireHookPayload) => void): void;
        on(event: string, callback: (...args: unknown[]) => void): () => void;
        dispatch(event: string, params?: Record<string, unknown>): void;
    }

    export const Livewire: LivewireGlobal;
    export const Alpine: AlpineInstance;
}
