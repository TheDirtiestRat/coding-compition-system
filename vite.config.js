import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

import monacoEditorEsmPlugin from 'vite-plugin-monaco-editor-esm'; 

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        monacoEditorEsmPlugin({
            // Optional: configure CDN for workers if needed, e.g., for production
            // monacoEditor: {
            //     publicPath: '/build/', // Or '/assets/' if using default Laravel setup
            // },
        }),
    ],
});
