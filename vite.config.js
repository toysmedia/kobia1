import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/css/dark-mode.css',
                'resources/css/responsive.css',
                'resources/css/print.css',
                'resources/js/dark-mode.js',
                'resources/js/onboarding-tour.js',
                'resources/js/subscription-countdown.js',
                'resources/js/notifications-poll.js',
                'resources/js/support-chat.js',
                'resources/js/ai-chat.js',
                'resources/js/performance.js',
                'resources/js/scroll-to-top.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        // Enable source maps in production for debugging
        sourcemap: false,
        // Target modern browsers
        target: 'es2018',
        // CSS minification via lightningcss or esbuild (vite default)
        cssMinify: true,
        // JS minification
        minify: 'esbuild',
        // Rollup options for code splitting
        rollupOptions: {
            output: {
                // Vendor chunk splitting
                manualChunks(id) {
                    if (id.includes('node_modules/vue')) return 'vendor-vue';
                    if (id.includes('node_modules/bootstrap')) return 'vendor-bootstrap';
                    if (id.includes('node_modules/chart.js') || id.includes('node_modules/vue-chartjs')) return 'vendor-charts';
                    if (id.includes('node_modules/shepherd')) return 'vendor-shepherd';
                    if (id.includes('node_modules/')) return 'vendor';
                },
                // Cache-busting asset file names
                assetFileNames: 'assets/[name]-[hash][extname]',
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
            },
        },
        // Inline assets smaller than 4KB
        assetsInlineLimit: 4096,
        // Enable chunk size reporting
        chunkSizeWarningLimit: 1000,
    },
});
