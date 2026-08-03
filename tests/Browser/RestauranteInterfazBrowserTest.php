<?php

declare(strict_types=1);

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Pages\Restaurante\CocinaPedidos;
use App\Filament\Pages\Restaurante\GestionMesas;
use App\Filament\Pages\Restaurante\PantallaPedidos;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    if (Moneda::query()->where('codigo', 'NIO')->doesntExist()) {
        Moneda::query()->create([
            'codigo' => 'NIO',
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]);
    }
});

test('un usuario autenticado accede a la pantalla de gestión de mesas', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(GestionMesas::class)
        ->assertSuccessful();
});

test('un usuario autenticado accede a la pantalla KDS de turnos y comanda', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(PantallaPedidos::class)
        ->assertSuccessful();
});

test('un usuario autenticado accede a la vista de pedidos de cocina', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(CocinaPedidos::class)
        ->assertSuccessful();
});
