import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import { pdfViewer } from 'vite-plugin-pdfjs-viewer';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Http/Livewire/**',
            ],
        }),
        pdfViewer({
            out: "public/",
            // base: "build/pdf-viewer/"
        }),
    ],
});
