<?php

declare(strict_types=1);

namespace Tests\Feature\Reportes;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use Filament\Widgets\Widget;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

final class TestWidgetRangoDashboard extends Widget
{
    use UsaRangoFechasDashboard;

    protected string $view = 'filament.pages.reportes.widgets.test-widget';

    /** @var array{inicio: string, fin: string} */
    public array $rangoActual = ['inicio' => '', 'fin' => ''];

    public function calcularRango(): void
    {
        $this->rangoActual = $this->rangoDashboard();
    }
}

it('usa el mes actual cuando no hay filtros de la página', function (): void {
    Livewire::test(TestWidgetRangoDashboard::class)
        ->call('calcularRango')
        ->assertSet('rangoActual', [
            'inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fin' => now()->format('Y-m-d'),
        ]);
});

it('usa el rango definido por la página cuando el widget recibe los filtros', function (): void {
    Livewire::test(TestWidgetRangoDashboard::class, [
        'pageFilters' => [
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-31',
        ],
    ])
        ->call('calcularRango')
        ->assertSet('rangoActual', [
            'inicio' => '2026-01-01',
            'fin' => '2026-01-31',
        ]);
});

it('ignora filtros de la página sin fechas válidas', function (): void {
    Livewire::test(TestWidgetRangoDashboard::class, [
        'pageFilters' => [],
    ])
        ->call('calcularRango')
        ->assertSet('rangoActual', [
            'inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fin' => now()->format('Y-m-d'),
        ]);
});
