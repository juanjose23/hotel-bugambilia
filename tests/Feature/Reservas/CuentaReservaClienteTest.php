<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Operaciones\RegistrarCobroInicialReserva;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Pais;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\MonedaSeeder;
use Database\Seeders\PaisSeeder;

test('guarda la persona del cliente en la cuenta creada para la reserva', function (): void {
    $this->seed([
        PaisSeeder::class,
        MonedaSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
    ]);

    $pais = Pais::query()->firstOrFail();
    $persona = Persona::factory()->create(['pais_id' => $pais->id]);
    $catalogo = Catalogo::query()->firstOrFail();
    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'estado' => EstadoGeneral::Activo,
    ]);
    $moneda = Moneda::query()->where('es_predeterminada', true)->firstOrFail();
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CLIENTE-CUENTA-001',
        'cliente_id' => $cliente->id,
        'nombre_cliente' => 'Cliente registrado',
        'tipo_reserva' => TipoReserva::SERVICIO,
        'moneda_id' => $moneda->id,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'adultos' => 1,
        'estado' => EstadoReserva::PENDIENTE,
        'subtotal' => 100,
        'descuento' => 0,
        'total' => 100,
    ]);

    app(RegistrarCobroInicialReserva::class)->ejecutar(
        reserva: $reserva,
        tipoPago: TipoPagoReserva::SIN_PAGO,
        monedaId: $moneda->id,
        metodoPago: null,
        referencia: null,
        usuarioId: null,
    );

    $cuenta = $reserva->cuentas()->firstOrFail();

    expect($cuenta->cliente_id)->toBe($cliente->id)
        ->and($cuenta->reserva_id)->toBe($reserva->id);
});
