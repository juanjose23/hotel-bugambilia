<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Repository\Models\Catalogos\Pais;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionItem;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;

test('calcula y crea correctamente una reserva de paquete hibrido combinando habitacion espacio y servicios', function (): void {
    $pais = Pais::query()->firstOrCreate(
        ['codigo_iso2' => 'NI'],
        ['codigo_iso3' => 'NIC', 'nombre' => 'Nicaragua', 'codigo_telefono' => '+505', 'estado' => 1]
    );

    $moneda = Moneda::query()->where('es_predeterminada', true)->first() ?? Moneda::query()->create([
        'codigo' => 'NIO',
        'nombre' => 'Córdoba',
        'simbolo' => 'C$',
        'es_predeterminada' => true,
        'estado' => 1,
    ]);

    $persona = Persona::factory()->create(['pais_id' => $pais->id]);
    $moneda = Moneda::query()->where('es_predeterminada', true)->firstOrFail();

    $habitacion = Habitacion::factory()->create(['estado' => EstadoEspacio::Disponible]);
    Precio::query()->create([
        'priceable_type' => Habitacion::class,
        'priceable_id' => $habitacion->id,
        'tipo_precio' => 'base',
        'precio' => 1000,
        'moneda_id' => $moneda->id,
        'estado' => EstadoGeneral::Activo,
        'fecha_inicio' => now()->subDay()->toDateString(),
    ]);

    $espacio = Espacio::query()->create([
        'nombre' => 'Mesa VIP Romance',
        'codigo' => 'M-VIP-R1',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 2,
        'estado' => 1,
    ]);
    Precio::query()->create([
        'priceable_type' => Espacio::class,
        'priceable_id' => $espacio->id,
        'tipo_precio' => 'base',
        'precio' => 500,
        'moneda_id' => $moneda->id,
        'estado' => EstadoGeneral::Activo,
        'fecha_inicio' => now()->subDay()->toDateString(),
    ]);

    $servicio = Servicio::query()->create([
        'nombre' => 'Masaje Spa Pareja',
        'codigo' => 'SPA-01',
        'precio_base' => 500,
        'estado' => 1,
    ]);
    Precio::query()->create([
        'priceable_type' => Servicio::class,
        'priceable_id' => $servicio->id,
        'tipo_precio' => 'base',
        'precio' => 500,
        'moneda_id' => $moneda->id,
        'estado' => EstadoGeneral::Activo,
        'fecha_inicio' => now()->subDay()->toDateString(),
    ]);

    $promocion = Promocion::query()->create([
        'codigo' => 'PAQ-ROMANTICO-VIP',
        'nombre' => 'Escapada Romántica VIP',
        'precio_paquete' => 1500,
        'fecha_inicio' => now()->subDay()->toDateString(),
        'fecha_fin' => now()->addDays(30)->toDateString(),
        'estado' => 1,
        'web' => true,
    ]);

    PromocionItem::query()->create([
        'promocion_id' => $promocion->id,
        'item_type' => Habitacion::class,
        'item_id' => $habitacion->id,
    ]);

    PromocionItem::query()->create([
        'promocion_id' => $promocion->id,
        'item_type' => Servicio::class,
        'item_id' => $servicio->id,
    ]);

    $datosReserva = [
        'nombre_cliente' => 'Cliente Paquete VIP',
        'email_cliente' => 'vip@example.com',
        'tipo_reserva' => TipoReserva::PAQUETE->value,
        'habitacion_id' => $habitacion->id,
        'espacio_id' => $espacio->id,
        'promocion_id' => $promocion->id,
        'moneda_id' => $moneda->id,
        'fecha_check_in' => now()->addDays(2)->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'hora_reserva' => '20:00',
        'adultos' => 2,
        'ninos' => 0,
        'servicios_adicionales' => [
            [
                'servicio_id' => $servicio->id,
                'cantidad' => 1,
            ],
        ],
    ];

    /** @var CrearReserva $interactor */
    $interactor = app(CrearReserva::class);
    $reserva = $interactor->ejecutar($datosReserva);

    expect($reserva)->not->toBeNull()
        ->and($reserva->tipo_reserva)->toBe(TipoReserva::PAQUETE)
        ->and($reserva->promocion_id)->toBe($promocion->id)
        ->and($reserva->habitacion_id)->toBe($habitacion->id)
        ->and($reserva->espacio_id)->toBe($espacio->id)
        ->and($reserva->estado)->toBe(EstadoReserva::PENDIENTE)
        ->and((float) $reserva->subtotal)->toBe(1500.0)
        ->and((float) $reserva->total)->toBe(1725.0);
});
