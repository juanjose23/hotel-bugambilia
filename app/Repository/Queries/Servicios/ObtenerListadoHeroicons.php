<?php

declare(strict_types=1);

namespace App\Repository\Queries\Servicios;

use Illuminate\Support\Facades\Cache;

class ObtenerListadoHeroicons
{
    /**
     * Equivalencias semánticas entre nombres de Lucide y Heroicons para el panel administrativo.
     *
     * @var array<string, string>
     */
    private const array LUCIDE_A_HEROICON = [
        'log-out' => 'arrow-right-start-on-rectangle',
        'log-in' => 'arrow-right-end-on-rectangle',
        'logout' => 'arrow-right-start-on-rectangle',
        'login' => 'arrow-right-end-on-rectangle',
        'bed' => 'home',
        'bed-double' => 'home',
        'bed-single' => 'home',
        'tv' => 'computer-desktop',
        'coffee' => 'cake',
        'utensils' => 'building-storefront',
        'restaurant' => 'building-storefront',
        'bath' => 'home-modern',
        'shower-head' => 'home-modern',
        'car' => 'truck',
        'parking' => 'truck',
        'gym' => 'trophy',
        'dumbbell' => 'trophy',
        'laundry' => 'scissors',
        'shirt' => 'scissors',
        'concierge-bell' => 'bell',
        'bell-ring' => 'bell',
        'ac' => 'sun',
        'wind' => 'sun',
        'waves' => 'lifebuoy',
        'swimming' => 'lifebuoy',
        'pool' => 'lifebuoy',
        'plane' => 'paper-airplane',
        'card' => 'credit-card',
        'card-holder' => 'credit-card',
        'briefcase' => 'briefcase',
        'cocktail' => 'cake',
        'glass-water' => 'beaker',
        'wine' => 'cake',
        'zap' => 'bolt',
        'shield' => 'shield-check',
        'search' => 'magnifying-glass',
        'mail' => 'envelope',
        'send' => 'paper-airplane',
        'calendar-check' => 'calendar-days',
        'file-text' => 'document-text',
    ];

    /**
     * Resuelve el nombre del icono para su renderizado en Filament/Blade validando la existencia física del SVG.
     */
    public static function resolverParaFilament(?string $icono): string
    {
        if (blank($icono)) {
            return 'heroicon-o-check-badge';
        }

        $raw = strtolower(trim((string) $icono));
        $clean = str_replace(['heroicon-o-', 'heroicon-m-', 'heroicon-s-', 'heroicon-c-'], '', $raw);

        // Si existe mapeo conocido de Lucide a Heroicon
        if (isset(self::LUCIDE_A_HEROICON[$clean])) {
            $clean = self::LUCIDE_A_HEROICON[$clean];
        }

        /** @var array<string, bool> $disponibles */
        $disponibles = Cache::rememberForever('blade_heroicons_outline_existing_v1', function (): array {
            $svgPath = base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg');
            $valid = [];
            if (is_dir($svgPath)) {
                $files = scandir($svgPath);
                if ($files !== false) {
                    foreach ($files as $file) {
                        if (str_starts_with($file, 'o-') && str_ends_with($file, '.svg')) {
                            $name = substr($file, 2, -4);
                            $valid[$name] = true;
                        }
                    }
                }
            }

            return $valid;
        });

        if (isset($disponibles[$clean])) {
            return 'heroicon-o-'.$clean;
        }

        return 'heroicon-o-check-badge';
    }

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
