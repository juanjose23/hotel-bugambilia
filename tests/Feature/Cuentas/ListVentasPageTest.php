<?php

declare(strict_types=1);

namespace Tests\Feature\Cuentas;

use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Resources\Cuentas\VentaResource\Pages\ListVentas;
use App\Filament\Resources\Cuentas\VentaResource\Widgets\VentaStatsOverview;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('ListVentas page mounts correctly with header widgets and tabs', function (): void {
    $user = User::factory()->create();
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $moneda = Moneda::query()->firstOrCreate(
        ['codigo' => 'NIO'],
        [
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]
    );

    Venta::query()->create([
        'numero_venta' => 'VNT-PAGE-TEST-001',
        'subtotal' => 500.00,
        'impuesto_total' => 75.00,
        'descuento_total' => 0.00,
        'servicio_total' => 0.00,
        'propina_total' => 0.00,
        'recargo_total' => 0.00,
        'total' => 575.00,
        'estado' => EstadoVenta::Emitida,
        'moneda_id' => $moneda->id,
        'created_at' => now(),
    ]);

    Livewire::test(ListVentas::class)
        ->assertSuccessful()
        ->assertSee('VNT-PAGE-TEST-001');

    Livewire::test(VentaStatsOverview::class)
        ->assertSuccessful()
        ->assertSee('Total Ventas')
        ->assertSee('Ticket Promedio');
});
