<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Filament\Pages\Activos\ReportesActivos;
use App\Filament\Pages\Compras\ReportesCompras;
use App\Filament\Pages\Inventario\ReportesInventario;
use App\Filament\Pages\Limpieza\ReportesLimpieza;
use App\Filament\Pages\Reportes\ReportesEjecutivos;
use App\Filament\Pages\Reservas\ReportesReservas;
use App\Filament\Pages\Servicios\ReporteHistoricoPrecios;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Page:ReportesRestaurante', 'guard_name' => 'web']);

    if (! Espacio::tieneRestauranteActivo()) {
        Espacio::create([
            'codigo' => 'REST-01',
            'nombre' => 'Restaurante Central',
            'tipo' => TipoEspacio::RESTAURANTE,
            'estado' => EstadoEspacio::Disponible,
            'capacidad' => 50,
            'web' => true,
        ]);
    }

    $admin = User::first() ?? User::factory()->create();
    $admin->is_admin = true;
    $admin->save();
    $admin->assignRole('super_admin');
    $admin->givePermissionTo('Page:ReportesRestaurante');
    $this->actingAs($admin);
});

describe('Acceso HTTP a todas las páginas de reportes de Filament', function (): void {
    test('Página Reportes Compras responde HTTP 200', function (): void {
        $response = $this->get('/admin/reportes-compras');
        $response->assertOk();
    });

    test('Página Reportes Inventario responde HTTP 200', function (): void {
        $response = $this->get('/admin/reportes-inventario');
        $response->assertOk();
    });

    test('Página Reportes Activos responde HTTP 200', function (): void {
        $response = $this->get('/admin/reportes-activos');
        $response->assertOk();
    });

    test('Página Reportes Limpieza responde HTTP 200', function (): void {
        $response = $this->get('/admin/reportes-limpieza');
        $response->assertOk();
    });

    test('Página Reportes Reservas responde HTTP 200', function (): void {
        $response = $this->get('/admin/reportes-reservas');
        $response->assertOk();
    });

    test('Página Reportes Ejecutivos responde HTTP 200', function (): void {
        $response = $this->get('/admin/reportes-ejecutivos');
        $response->assertOk();
    });

    test('Página Reportes Servicios responde HTTP 200', function (): void {
        $response = $this->get('/admin/reporte-historico-precios-servicios');
        $response->assertOk();
    });

    test('Página Reportes Restaurante responde HTTP 200', function (): void {
        $response = $this->get('/admin/restaurante/reportes');
        $response->assertOk();
    });
});

describe('Ejecución de acción descargarReporte en Livewire de cada página de reportes', function (): void {
    test('Livewire ReportesCompras genera reporte correctamente', function (): void {
        $component = Livewire::test(ReportesCompras::class)
            ->call('descargarReporte');

        $component->assertHasNoErrors();
        expect($component->get('reporteListoUrl'))->not->toBeNull();
    });

    test('Livewire ReportesInventario genera reporte correctamente', function (): void {
        $component = Livewire::test(ReportesInventario::class)
            ->call('descargarReporte');

        $component->assertHasNoErrors();
        expect($component->get('reporteListoUrl'))->not->toBeNull();
    });

    test('Livewire ReportesActivos genera reporte correctamente', function (): void {
        $component = Livewire::test(ReportesActivos::class)
            ->call('descargarReporte');

        $component->assertHasNoErrors();
        expect($component->get('reporteListoUrl'))->not->toBeNull();
    });

    test('Livewire ReportesLimpieza genera reporte correctamente', function (): void {
        $component = Livewire::test(ReportesLimpieza::class)
            ->call('descargarReporte');

        $component->assertHasNoErrors();
        expect($component->get('reporteListoUrl'))->not->toBeNull();
    });

    test('Livewire ReportesReservas genera reporte correctamente', function (): void {
        $component = Livewire::test(ReportesReservas::class)
            ->call('descargarReporte');

        $component->assertHasNoErrors();
        expect($component->get('reporteListoUrl'))->not->toBeNull();
    });

    test('Livewire ReportesEjecutivos genera reporte correctamente', function (): void {
        $component = Livewire::test(ReportesEjecutivos::class)
            ->call('descargarReporte');

        $component->assertHasNoErrors();
        expect($component->get('reporteListoUrl'))->not->toBeNull();
    });

    test('Livewire ReporteHistoricoPrecios genera reporte correctamente', function (): void {
        $component = Livewire::test(ReporteHistoricoPrecios::class)
            ->call('descargarReporte');

        $component->assertHasNoErrors();
        expect($component->get('reporteListoUrl'))->not->toBeNull();
    });
});
