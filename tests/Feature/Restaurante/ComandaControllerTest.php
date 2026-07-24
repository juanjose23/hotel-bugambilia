<?php

declare(strict_types=1);

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use Illuminate\Support\Facades\Gate;

function pedidoParaComanda(): Pedido
{
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-'.str()->random(8),
        'nombre' => 'Mesa Comanda',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);

    return Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-'.str()->random(8),
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::ABIERTO,
        'total' => 0,
    ]));
}

it('requiere autenticación para imprimir una comanda', function (): void {
    $pedido = pedidoParaComanda();

    $this->get(route('admin.restaurante.comanda', $pedido))
        ->assertRedirect(route('login'));
});

it('rechaza usuarios sin permiso para imprimir comandas', function (): void {
    $pedido = pedidoParaComanda();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.restaurante.comanda', $pedido))
        ->assertForbidden();
});

it('permite imprimir la comanda con autorización', function (): void {
    $pedido = pedidoParaComanda();
    Gate::define('viewComanda', fn (): bool => true);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.restaurante.comanda', $pedido))
        ->assertSuccessful();
});
