import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        // El stage de Node del Dockerfile no tiene PHP: los archivos de
        // resources/js/{actions,routes} ya vienen generados por el stage PHP
        // previo, así que este flag evita que el plugin intente regenerarlos.
        ...(process.env.WAYFINDER_SKIP
            ? []
            : [
                  wayfinder({
                      formVariants: true,
                  }),
              ]),
    ],
});
