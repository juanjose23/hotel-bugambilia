<?php

declare(strict_types=1);

namespace App\Repository\Queries\Servicios;

use Illuminate\Support\Facades\Cache;

class ObtenerListadoHeroicons
{
    /** @return array<string, string> */
    public function ejecutar(): array
    {
        return Cache::rememberForever('lucide_react_node_modules_icons_list_v4', function () {
            $lucideIcons = $this->loadLucideIconsFromNodeModules();

            if ($lucideIcons === []) {
                return $this->loadHeroiconsFromFilesystem();
            }

            return $lucideIcons;
        });
    }

    /**
     * Carga todos los iconos de Lucide disponibles directamente desde node_modules/lucide-react.
     *
     * @return array<string, string>
     */
    private function loadLucideIconsFromNodeModules(): array
    {
        $path = base_path('node_modules/lucide-react/dist/esm/icons');
        $icons = [];

        if (! is_dir($path)) {
            return $icons;
        }

        $files = scandir($path);
        if ($files === false) {
            return $icons;
        }

        foreach ($files as $file) {
            if (str_ends_with($file, '.mjs') && ! str_ends_with($file, '.mjs.map')) {
                $name = substr($file, 0, -4); // ej: "wifi", "concierge-bell", "coffee"
                $label = ucwords(str_replace('-', ' ', $name)).' (Lucide React)';
                $icons[$name] = $label;
            }
        }

        ksort($icons);

        return $icons;
    }

    /**
     * Fallback si node_modules no estuviera presente.
     *
     * @return array<string, string>
     */
    private function loadHeroiconsFromFilesystem(): array
    {
        $svgPath = base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg');
        $icons = [];

        if (! is_dir($svgPath)) {
            return $icons;
        }

        $files = scandir($svgPath);
        if ($files === false) {
            return $icons;
        }

        foreach ($files as $file) {
            if (str_starts_with($file, 'o-') && str_ends_with($file, '.svg')) {
                $name = substr($file, 2, -4);
                $key = 'heroicon-o-'.$name;
                $label = ucwords(str_replace('-', ' ', $name)).' (Heroicon)';
                $icons[$key] = $label;
            }
        }

        ksort($icons);

        return $icons;
    }
}
