<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Mesas\SepararMesas;
use App\Interactors\Restaurante\Mesas\UnirMesas;
use App\Interactors\Restaurante\Pedidos\AbrirPedidoMesa;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;

test('permite abrir multiples cuentas independientes en la misma mesa', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-MULTI-1',
        'nombre' => 'Mesa Central 01',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
        'activo' => true,
    ]);

    $abrirInteractor = app(AbrirPedidoMesa::class);
    $cuenta1 = $abrirInteractor->ejecutar($mesa, null, null, 'Cuenta Juan');

    $mesa->update(['estado' => EstadoEspacio::Disponible]);
    $cuenta2 = $abrirInteractor->ejecutar($mesa, null, null, 'Cuenta Pedro');

    expect($cuenta1->estado)->toBe(EstadoPedido::ABIERTO)
        ->and($cuenta2->estado)->toBe(EstadoPedido::ABIERTO)
        ->and($cuenta1->codigo)->not->toBe($cuenta2->codigo);
});

test('permite unir mesas secundarias a una mesa principal para uso inmediato en servicio', function (): void {
    $mesaPrincipal = Espacio::query()->create([
        'codigo' => 'MESA-PRI-01',
        'nombre' => 'Mesa 01',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $mesaSecundaria = Espacio::query()->create([
        'codigo' => 'MESA-SEC-02',
        'nombre' => 'Mesa 02',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $unirInteractor = app(UnirMesas::class);
    $unirInteractor->ejecutar($mesaPrincipal->id, [$mesaSecundaria->id], null, 'uso_inmediato');

    $mesaSecundaria->refresh();
    $mesaPrincipal->refresh();
    $metaSec = is_array($mesaSecundaria->meta_datos) ? $mesaSecundaria->meta_datos : [];
    $metaPri = is_array($mesaPrincipal->meta_datos) ? $mesaPrincipal->meta_datos : [];

    expect($mesaSecundaria->estado)->toBe(EstadoEspacio::Ocupado)
        ->and($metaSec['mesa_principal_id'] ?? null)->toBe($mesaPrincipal->id)
        ->and($metaPri['mesas_unidas'] ?? [])->toContain($mesaSecundaria->id);
});

test('no bloquea mesas asociadas a una reservacion futura de grupo', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-GRUPO-01',
        'nombre_cliente' => 'Grupo Corporativo VIP',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    $mesaPrincipal = Espacio::query()->create([
        'codigo' => 'MESA-RES-10',
        'nombre' => 'Mesa 10',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $mesaSecundaria = Espacio::query()->create([
        'codigo' => 'MESA-RES-11',
        'nombre' => 'Mesa 11',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $unirInteractor = app(UnirMesas::class);
    $unirInteractor->ejecutar($mesaPrincipal->id, [$mesaSecundaria->id], $reserva->id, 'reservacion');

    $mesaSecundaria->refresh();
    $mesaPrincipal->refresh();
    $metaSecRes = is_array($mesaSecundaria->meta_datos) ? $mesaSecundaria->meta_datos : [];
    $metaPriRes = is_array($mesaPrincipal->meta_datos) ? $mesaPrincipal->meta_datos : [];

    expect($mesaSecundaria->estado)->toBe(EstadoEspacio::Disponible)
        ->and($metaSecRes['reserva_id'] ?? null)->toBeNull()
        ->and($mesaPrincipal->estado)->toBe(EstadoEspacio::Disponible)
        ->and($metaPriRes['codigo_reserva'] ?? null)->toBeNull();
});

test('permite separar mesas previamente unidas y las vuelve disponibles', function (): void {
    $mesaPrincipal = Espacio::query()->create([
        'codigo' => 'MESA-PRI-03',
        'nombre' => 'Mesa 03',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $mesaSecundaria = Espacio::query()->create([
        'codigo' => 'MESA-SEC-04',
        'nombre' => 'Mesa 04',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $unirInteractor = app(UnirMesas::class);
    $unirInteractor->ejecutar($mesaPrincipal->id, [$mesaSecundaria->id]);

    $separarInteractor = app(SepararMesas::class);
    $separarInteractor->ejecutar($mesaPrincipal->id);

    expect($mesaSecundaria->refresh()->estado)->toBe(EstadoEspacio::Disponible)
        ->and($mesaPrincipal->refresh()->meta_datos['mesas_unidas'] ?? [])->toBeEmpty();
});

test('rechaza unir una mesa a si misma', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-SELF',
        'nombre' => 'Mesa Self',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $unirInteractor = app(UnirMesas::class);
    $unirInteractor->ejecutar($mesa->id, [$mesa->id]);
})->throws(DomainException::class, 'no puede unirse a sí misma');
