import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/ts/app.ts'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            // Livewire's ESM bundle (includes Alpine). Typed in resources/ts/types/livewire.d.ts.
            '@livewire': fileURLToPath(new URL('./vendor/livewire/livewire/dist/livewire.esm.js', import.meta.url)),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
