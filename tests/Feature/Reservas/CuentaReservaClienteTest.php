<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\RegistrarCobroInicialReserva;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Database\Seeders\MonedaSeeder;

test('guarda la persona del cliente en la cuenta creada para la reserva', function (): void {
    $this->seed(MonedaSeeder::class);

    $persona = Persona::factory()->create();
    $usuario = User::factory()->create(['persona_id' => $persona->id]);
    $moneda = Moneda::query()->where('es_predeterminada', true)->firstOrFail();
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CLIENTE-CUENTA-001',
        'cliente_id' => $usuario->id,
        'nombre_cliente' => 'Cliente registrado',
        'tipo_reserva' => TipoReserva::SERVICIO,
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

    expect($cuenta->cliente_id)->toBe($persona->id)
        ->and($cuenta->reserva_id)->toBe($reserva->id);
});
