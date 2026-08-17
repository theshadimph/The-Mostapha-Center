import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                /* @chisel-passkeys */
                'resources/js/passkeys.js',
                /* @end-chisel-passkeys */
            ],
            refresh: true,
            fonts: [
                bunny('IBM Plex Sans Arabic', {
                    weights: [300, 400, 500, 600, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        // Windows resolves this project's Herd domain with inconsistent
        // casing (APP_URL is lowercase, but Vite's auto-detected network
        // host came back as "The-Mostapha-Center.test") — the HMR
        // WebSocket's Origin check then rejects the browser's lowercase
        // origin with a 400. Pinning both to APP_URL's exact casing fixes
        // the handshake.
        host: 'the-mostapha-center.test',
        hmr: {
            host: 'the-mostapha-center.test',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
