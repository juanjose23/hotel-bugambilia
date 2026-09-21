<?php

declare(strict_types=1);

namespace Tests\Feature\Reportes;

use App\Filament\Pages\Reportes\TableroInteligenciaNegocio;
use App\Filament\Pages\Reportes\Widgets\AlertasOperacionWidget;
use App\Filament\Pages\Reportes\Widgets\AnomaliasOperacionWidget;
use App\Filament\Pages\Reportes\Widgets\EmbudoConversionWidget;
use App\Filament\Pages\Reportes\Widgets\IngresosReservasChart;
use App\Filament\Pages\Reportes\Widgets\KpisInteligenciaNegocioWidget;
use App\Filament\Pages\Reportes\Widgets\PromocionesInteligenciaNegocioWidget;
use App\Filament\Pages\Reportes\Widgets\RadialOcupacionWidget;
use App\Filament\Pages\Reportes\Widgets\ReservasEstadoChart;
use App\Filament\Pages\Reportes\Widgets\ResumenOperacionWidget;
use App\Filament\Pages\Reportes\Widgets\ResumenRestauranteWidget;
use App\Filament\Pages\Reportes\Widgets\TendenciaTemporadaChart;
use App\Filament\Pages\Reportes\Widgets\TopPlatosChart;
use App\Filament\Pages\Reportes\Widgets\VentasPorHoraChart;
use App\Repository\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class TableroInteligenciaNegocioTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('Page:TableroInteligenciaNegocio');
    }

    public function test_tablero_inicializa_con_filtros_de_mes_actual(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Page:TableroInteligenciaNegocio');

        $this->actingAs($user);

        $component = Livewire::test(TableroInteligenciaNegocio::class);
        $component->assertSuccessful();
        $component->assertSet('filters.preset', 'este_mes');
        $component->assertSet('filters.fecha_inicio', fn ($value): bool => is_string($value) && str_starts_with($value, now()->startOfMonth()->format('Y-m-d')));
        $component->assertSet('filters.fecha_fin', fn ($value): bool => is_string($value) && str_starts_with($value, now()->format('Y-m-d')));
    }

    public function test_cambiar_preset_actualiza_fechas_automaticamente(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Page:TableroInteligenciaNegocio');

        $this->actingAs($user);

        $hoy = CarbonImmutable::now();

        Livewire::test(TableroInteligenciaNegocio::class)
            ->set('filters.preset', 'hoy')
            ->assertSet('filters.fecha_inicio', fn ($value): bool => is_string($value) && str_starts_with($value, $hoy->format('Y-m-d')))
            ->assertSet('filters.fecha_fin', fn ($value): bool => is_string($value) && str_starts_with($value, $hoy->format('Y-m-d')))
            ->set('filters.preset', 'ultimos_7_dias')
            ->assertSet('filters.fecha_inicio', fn ($value): bool => is_string($value) && str_starts_with($value, $hoy->subDays(6)->format('Y-m-d')))
            ->assertSet('filters.fecha_fin', fn ($value): bool => is_string($value) && str_starts_with($value, $hoy->format('Y-m-d')));
    }

    public function test_jerarquia_analitica_de_widgets_ordenada_correctamente(): void
    {
        $page = new TableroInteligenciaNegocio;
        $widgets = $page->getWidgets();

        $this->assertCount(13, $widgets);

        // Nivel 1: Scorecard Ejecutivo
        $this->assertEquals(KpisInteligenciaNegocioWidget::class, $widgets[0]);

        // Nivel 2: Capacidad & Ocupación
        $this->assertEquals(RadialOcupacionWidget::class, $widgets[1]);

        // Nivel 3: Evolución Temporal
        $this->assertEquals(IngresosReservasChart::class, $widgets[2]);
        $this->assertEquals(TendenciaTemporadaChart::class, $widgets[3]);
        $this->assertEquals(ReservasEstadoChart::class, $widgets[4]);

        // Nivel 4: Embudo & Ventas por hora
        $this->assertEquals(EmbudoConversionWidget::class, $widgets[5]);
        $this->assertEquals(VentasPorHoraChart::class, $widgets[6]);

        // Nivel 5: Restaurante & Menú
        $this->assertEquals(ResumenRestauranteWidget::class, $widgets[7]);
        $this->assertEquals(TopPlatosChart::class, $widgets[8]);

        // Nivel 6: Promociones
        $this->assertEquals(PromocionesInteligenciaNegocioWidget::class, $widgets[9]);

        // Nivel 7: Operaciones & Riesgos
        $this->assertEquals(ResumenOperacionWidget::class, $widgets[10]);
        $this->assertEquals(AnomaliasOperacionWidget::class, $widgets[11]);
        $this->assertEquals(AlertasOperacionWidget::class, $widgets[12]);
    }
}
