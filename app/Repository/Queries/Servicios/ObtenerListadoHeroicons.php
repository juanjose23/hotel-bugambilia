<?php

declare(strict_types=1);

namespace App\Repository\Queries\Servicios;

use Illuminate\Support\Facades\Cache;

class ObtenerListadoHeroicons
{
    /** @return array<string, string> */
    public function ejecutar(): array
    {
        return Cache::rememberForever('heroicons_outline_list', function () {
            return $this->loadHeroiconsFromFilesystem();
        });
    }

    /**
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
                $label = ucwords(str_replace('-', ' ', $name));
                $icons[$key] = $label;
            }
        }

        asort($icons);

        return $icons;
    }
}
