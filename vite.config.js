import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

console.log(__dirname);

export default defineConfig({
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin'],
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Listen on all network interfaces,
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'dev.flashy.com',
        },
        watch: {
            usePolling: true,  // Important for Docker
        },
    },
    // resolve: {
    //     alias: {
    //         '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
    //     }
    // },
});
