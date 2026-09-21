<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets\Concerns;

use Filament\Widgets\Concerns\InteractsWithPageFilters;

trait UsaRangoFechasDashboard
{
    use InteractsWithPageFilters;

    /**
     * @return array{inicio: string, fin: string}
     */
    protected function rangoDashboard(): array
    {
        $filters = is_array($this->pageFilters) ? $this->pageFilters : [];
        $inicio = $filters['fecha_inicio'] ?? null;
        $fin = $filters['fecha_fin'] ?? null;

        return [
            'inicio' => is_string($inicio) ? $inicio : now()->startOfMonth()->format('Y-m-d'),
            'fin' => is_string($fin) ? $fin : now()->format('Y-m-d'),
        ];
    }
}
