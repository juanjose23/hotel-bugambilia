<?php

declare(strict_types=1);

use App\Enums\Cuentas\MetodoPago;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\HabitacionesEspacios\TipoPrecioEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Events\Reservas\ReservaCreada;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Precio;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-03 10:00:00');

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

test('crea reservación de restaurante con cliente asignado, mesas unidas y preorden de platillos', function (): void {
    Event::fake([ReservaCreada::class]);

    $tipoCatalogo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'TIPO-CLIENTE'],
        [
            'nombre' => 'Tipo de Cliente',
            'estado' => EstadoGeneral::Activo,
        ]
    );

    $catalogo = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT-CLI-GEN'],
        [
            'catalogo_tipo_id' => $tipoCatalogo->id,
            'nombre' => 'Cliente General',
            'estado' => EstadoGeneral::Activo,
        ]
    );

    $persona = Persona::query()->create([
        'tipo_persona' => 'natural',
        'primer_nombre' => 'Carlos',
        'segundo_nombre' => 'Alberto',
        'telefono' => '88889999',
        'email' => 'carlos@ejemplo.com',
    ]);

    PersonaNatural::query()->create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Mendoza',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '001-010190-0001A',
    ]);

    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'estado' => EstadoGeneral::Activo,
    ]);

    $mesaPrincipal = Espacio::query()->create([
        'nombre' => 'Mesa VIP 01',
        'codigo' => 'M-VIP-01',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'reservable' => true,
        'estado' => 1,
    ]);

    $moneda = Moneda::query()->where('codigo', 'NIO')->firstOrFail();

    Precio::query()->create([
        'priceable_id' => $mesaPrincipal->id,
        'priceable_type' => Espacio::class,
        'tipo_precio' => TipoPrecioEspacio::Base,
        'precio' => 500.00,
        'fecha_inicio' => now()->format('Y-m-d'),
        'moneda_id' => $moneda->id,
        'estado' => EstadoGeneral::Activo,
    ]);

    $mesaSecundaria = Espacio::query()->create([
        'nombre' => 'Mesa VIP 02',
        'codigo' => 'M-VIP-02',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'reservable' => true,
        'estado' => 1,
    ]);

    $plato = Plato::query()->create([
        'nombre' => 'Churrasco Bugambilia 12oz',
        'codigo' => 'PLT-CHURR-12',
        'precio_base' => 450.00,
        'estado' => EstadoGeneral::Activo,
    ]);

    $datos = [
        'cliente_id' => $cliente->id,
        'nombre_cliente' => 'Carlos Mendoza',
        'telefono_cliente' => '88889999',
        'email_cliente' => 'carlos@ejemplo.com',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $mesaPrincipal->id,
        'moneda_id' => $moneda->id,
        'fecha_check_in' => now()->format('Y-m-d'),
        'hora_reserva' => '19:00',
        'duracion_horas' => 2,
        'adultos' => 6,
        'cobrar_tarifa_mesa' => true,
        'items_preorden' => [
            [
                'plato_id' => $plato->id,
                'cantidad' => 2,
                'precio_unitario' => 450.00,
                'observaciones' => 'Término medio, papas fritas',
            ],
        ],
        'tipo_pago_reserva' => TipoPagoReserva::ABONO_50->value,
        'monto_pago_reserva' => 287.50,
        'metodo_pago_reserva' => MetodoPago::EFECTIVO->value,
    ];

    $espaciosAdicionales = [
        [
            'espacio_id' => $mesaSecundaria->id,
            'cantidad' => 1,
            'precio' => 0.00,
        ],
    ];

    /** @var CrearReserva $interactor */
    $interactor = app(CrearReserva::class);

    $reserva = $interactor->ejecutar($datos, [], $espaciosAdicionales);

    expect($reserva)->toBeInstanceOf(Reserva::class)
        ->and($reserva->cliente_id)->toBe($cliente->id)
        ->and($reserva->estado)->toBe(EstadoReserva::CONFIRMADA)
        ->and((float) $reserva->total)->toBe(575.00)
        ->and((float) $reserva->total_pagado)->toBe(287.50)
        ->and((float) $reserva->saldo)->toBe(287.50);

    // Verificar metadatos enriquecidos de preorden
    $bitacora = $reserva->ultimaEntradaBitacora('preorden');
    $platos = $bitacora['items'] ?? [];
    $primerPlato = is_array($platos[0] ?? null) ? $platos[0] : [];
    $subtotalPlatoVal = $primerPlato['subtotal'] ?? 0;
    $subtotalPlato = is_numeric($subtotalPlatoVal) ? (float) $subtotalPlatoVal : 0.0;

    expect($primerPlato['nombre'] ?? null)->toBe('Churrasco Bugambilia 12oz')
        ->and($subtotalPlato)->toBe(900.00);

    // Verificar mesas asociadas en los detalles de la reserva
    $detallesCount = $reserva->detalles()->count();
    expect($detallesCount)->toBeGreaterThanOrEqual(2);

    Event::assertDispatched(ReservaCreada::class);
});
