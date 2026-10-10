import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/vendor/base-1.css',
                'resources/css/vendor/base-2.css',
                'resources/js/vendor/register.js',
            ],
            refresh: true,
        }),
    ],
});
