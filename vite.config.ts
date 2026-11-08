import { fileURLToPath, URL } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig(({ command }) => {
    const laravelPlugins = laravel({
        input: [
            'resources/css/app.css',
            'resources/css/filament/admin/theme.css',
            'resources/js/app.tsx',
            'resources/js/filament/guias/index.ts',
        ],
        refresh: true,
    });

    // En 'build', el hook transform del plugin de Laravel es un no-op (solo opera en serve).
    // Omitirlo evita miles de llamadas IPC innecesarias entre Rolldown (Rust) y Node.js en Windows.
    const pluginsConfig = (Array.isArray(laravelPlugins) ? laravelPlugins : [laravelPlugins]).map((plugin) => {
        if (command === 'build' && plugin && typeof plugin === 'object' && plugin.name === 'laravel') {
            const { transform, ...resto } = plugin;
            return resto;
        }
        return plugin;
    });

    return {
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            },
        },
        server: {
            watch: {
                ignored: ['**/.local/**'],
            },
        },
        plugins: [
            ...pluginsConfig,
            react({
                exclude: [/\/node_modules\//, /\/\.local\//],
            }),
            tailwindcss(),
        ],
        build: {
            rollupOptions: {
                output: {
                    manualChunks(id) {
                        if (id.includes('node_modules')) {
                            if (id.includes('node_modules/driver.js/')) {
                                return 'driver';
                            }
                            if (id.includes('react') || id.includes('react-dom')) {
                                return 'vendor-react';
                            }
                            if (id.includes('lucide-react')) {
                                return 'vendor-icons';
                            }
                            if (id.includes('@inertiajs')) {
                                return 'vendor-inertia';
                            }
                            return 'vendor';
                        }
                    },
                },
            },
        },
    };
});
