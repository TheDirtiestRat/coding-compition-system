import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// import monacoEditorEsmPlugin from 'vite-plugin-monaco-editor-esm';

import monacoEditorPluginModule from 'vite-plugin-monaco-editor';

// Handle the "is not a function" error by checking for .default
const monacoEditorPlugin = monacoEditorPluginModule.default || monacoEditorPluginModule;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js','resources/js/admin.js','resources/js/users.js'],
            refresh: true,
        }),
        tailwindcss(),
        monacoEditorPlugin({
            languageWorkers: ['editorWorkerService', 'typescript', 'json', 'html']
        }),
        // monacoEditorEsmPlugin({
        //     // Optional: configure CDN for workers if needed, e.g., for production
        //     // monacoEditor: {
        //     //     publicPath: '/build/', // Or '/assets/' if using default Laravel setup
        //     // },
        // }),
    ],
});
