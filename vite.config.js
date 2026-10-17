import { globSync } from 'node:fs';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Every theme brings its own entry points, so a new theme folder is picked up automatically.
const themeEntries = globSync('resources/themes/*/{scss/app.scss,js/app.js}');

export default defineConfig({
    plugins: [
        laravel({
            input: themeEntries,
            refresh: ['resources/views/**', 'resources/themes/**', 'routes/**', 'config/**'],
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 5.3 still uses Sass @import; hide its deprecation noise.
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
