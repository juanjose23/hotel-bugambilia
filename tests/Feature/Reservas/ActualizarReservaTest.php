<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Gestion\ActualizarReserva;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Interactors\Reservas\Operaciones\UnirMesasReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\CalcularVistaPreviaFinancieraReservaQuery;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\EspacioSeeder;
use Database\Seeders\HabitacionSeeder;
use Database\Seeders\MonedaSeeder;
use Database\Seeders\PaisSeeder;
use Database\Seeders\ServicioSeeder;
use Database\Seeders\TasaCambioSeeder;
use Database\Seeders\UbicacionSeeder;

beforeEach(function (): void {
    $this->seed([
        PaisSeeder::class,
        MonedaSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
        TasaCambioSeeder::class,
        HabitacionSeeder::class,
        ServicioSeeder::class,
        EspacioSeeder::class,
    ]);
});

test('reemplaza los servicios y espacios adicionales al editar', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $servicio = Servicio::query()
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();
    $servicioNuevo = Servicio::query()
        ->where('id', '!=', $servicio->id)
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();
    $espacio = Espacio::query()
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva con adicionales',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [
        ['servicio_id' => $servicio->id, 'cantidad' => 1],
    ], []);

    expect($reserva->detalles()->count())->toBe(2);

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva, [
        'nombre_cliente' => 'Reserva con adicionales',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
        'servicios_adicionales' => [
            ['servicio_id' => $servicioNuevo->id, 'cantidad' => 2],
        ],
        'espacios_adicionales' => [
            ['espacio_id' => $espacio->id, 'cantidad' => 1],
        ],
    ]);

    $hijos = $actualizada->detalles()
        ->with(['reservable.servicio', 'reservable.espacio'])
        ->get()
        ->whereNotNull('parent_id')
        ->values();

    expect($actualizada->detalles()->count())->toBe(3)
        ->and($actualizada->detalles()->withTrashed()->count())->toBe(4)
        ->and($hijos)->toHaveCount(2)
        ->and($hijos->first()?->reservable?->servicio?->id)->toBe($servicioNuevo->id)
        ->and($hijos->first()?->cantidad)->toBe(2)
        ->and($hijos->last()?->reservable?->espacio?->id)->toBe($espacio->id);
});

test('reconstruye el detalle principal de una reserva legacy sin usar reservable cero', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-LEGACY-001',
        'nombre_cliente' => 'Reserva legacy',
        'tipo_reserva' => TipoReserva::HABITACION,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-20',
        'fecha_check_out' => '2026-11-21',
        'adultos' => 2,
        'estado' => EstadoReserva::CONFIRMADA,
        'subtotal' => 1000,
        'descuento' => 0,
        'total' => 1000,
    ]);

    $detalle = app(ReservaRepositorioInterface::class)->detallePrincipalDe($reserva);

    expect($detalle->reservable_id)->toBeGreaterThan(0)
        ->and($detalle->reservable)->not->toBeNull()
        ->and($detalle->reserva_id)->toBe($reserva->id);
});

test('recalcula los totales con las tarifas vigentes al editar', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $servicio = Servicio::query()
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva sin adicionales',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [], []);

    $datos = [
        'nombre_cliente' => 'Reserva sin adicionales',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
        'servicios_adicionales' => [
            ['servicio_id' => $servicio->id, 'cantidad' => 3],
        ],
    ];

    $esperado = app(CalcularVistaPreviaFinancieraReservaQuery::class)->ejecutar($datos);

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva, $datos);

    expect((float) $actualizada->subtotal)->toBe($esperado['subtotal'])
        ->and((float) $actualizada->descuento)->toBe($esperado['descuento'])
        ->and((float) $actualizada->total)->toBe($esperado['total'])
        ->and((float) $actualizada->saldo)->toBe(round($esperado['total'] - (float) $actualizada->total_pagado, 2));
});

test('permite cambiar el recurso principal y fechas antes del check in', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $habitacionNueva = Habitacion::query()->where('id', '!=', $habitacion->id)->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva cambio principal',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [], []);

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva, [
        'nombre_cliente' => 'Reserva cambio principal',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacionNueva->id,
        'fecha_check_in' => '2026-11-12',
        'fecha_check_out' => '2026-11-14',
        'adultos' => 3,
        'ninos' => 1,
    ]);

    $principal = $actualizada->detalles()->whereNull('parent_id')->firstOrFail();

    expect($actualizada->habitacion_id)->toBe($habitacionNueva->id)
        ->and($actualizada->fecha_check_in?->format('Y-m-d'))->toBe('2026-11-12')
        ->and($actualizada->fecha_check_out?->format('Y-m-d'))->toBe('2026-11-14')
        ->and($actualizada->adultos)->toBe(3)
        ->and($actualizada->ninos)->toBe(1)
        ->and($principal->reservable?->habitacion?->id)->toBe($habitacionNueva->id)
        ->and($principal->fecha_inicio->format('Y-m-d'))->toBe('2026-11-12')
        ->and($principal->fecha_fin?->format('Y-m-d'))->toBe('2026-11-14');
});

test('unir mesas reserva crea detalles reservables para mesas secundarias', function (): void {
    $mesaPrincipal = Espacio::query()->where('tipo', 'mesa')->firstOrFail();
    $mesaSecundaria = Espacio::query()
        ->where('tipo', 'mesa')
        ->where('id', '!=', $mesaPrincipal->id)
        ->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva unión mesas',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $mesaPrincipal->id,
        'fecha_check_in' => '2026-11-10',
        'hora_reserva' => '13:00',
        'duracion_horas' => 2,
        'adultos' => 2,
    ], [], []);

    $actualizada = app(UnirMesasReserva::class)->ejecutar(
        reserva: $reserva,
        mesaPrincipalId: (int) $mesaPrincipal->id,
        mesasSecundariasIds: [(int) $mesaSecundaria->id],
    );

    $idsMesasDetalle = $actualizada->detalles()
        ->with('reservable.espacio')
        ->get()
        ->map(fn ($detalle): ?int => $detalle->reservable?->espacio?->id)
        ->filter()
        ->values()
        ->all();

    expect($idsMesasDetalle)->toContain((int) $mesaPrincipal->id)
        ->and($idsMesasDetalle)->toContain((int) $mesaSecundaria->id)
        ->and($mesaSecundaria->refresh()->reservable_id)->not->toBeNull()
        ->and($mesaSecundaria->estado)->toBe(EstadoEspacio::Disponible);
});

test('al editar reemplaza una mesa sugerida guardada por la mesa elegida por el usuario', function (): void {
    $ambiente = Espacio::query()->create([
        'codigo' => 'AMB-EDIT-MANUAL-01',
        'nombre' => 'Ambiente Edit Manual',
        'tipo' => TipoEspacio::AMBIENTE,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => false,
        'capacidad_personas' => 0,
    ]);

    $mesaPrincipal = Espacio::query()->create([
        'codigo' => 'MESA-EDIT-MANUAL-01',
        'nombre' => 'Mesa Edit Manual 01',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 2,
    ]);
    $mesaSugerida = Espacio::query()->create([
        'codigo' => 'MESA-EDIT-MANUAL-02',
        'nombre' => 'Mesa Edit Manual 02',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);
    $mesaElegida = Espacio::query()->create([
        'codigo' => 'MESA-EDIT-MANUAL-03',
        'nombre' => 'Mesa Edit Manual 03',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva cambia mesa sugerida',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $mesaPrincipal->id,
        'fecha_check_in' => '2026-11-14',
        'hora_reserva' => '13:00',
        'duracion_horas' => 2,
        'adultos' => 6,
    ], [], [
        ['espacio_id' => $mesaSugerida->id, 'cantidad' => 1],
    ]);

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva, [
        'nombre_cliente' => 'Reserva cambia mesa sugerida',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $mesaPrincipal->id,
        'fecha_check_in' => '2026-11-14',
        'hora_reserva' => '13:00',
        'duracion_horas' => 2,
        'adultos' => 6,
        'espacios_adicionales' => [
            ['espacio_id' => $mesaElegida->id, 'cantidad' => 1],
        ],
    ]);

    $idsMesasActivas = $actualizada->detalles()
        ->with('reservable.espacio')
        ->get()
        ->map(fn ($detalle): ?int => $detalle->reservable?->espacio?->id)
        ->filter()
        ->values()
        ->all();

    expect($idsMesasActivas)->toContain((int) $mesaPrincipal->id)
        ->and($idsMesasActivas)->toContain((int) $mesaElegida->id)
        ->and($idsMesasActivas)->not->toContain((int) $mesaSugerida->id)
        ->and($actualizada->detalles()->whereNotNull('parent_id')->count())->toBe(1)
        ->and($mesaElegida->refresh()->reservable_id)->not->toBeNull();
});

test('al editar elimina adicionales viejos aunque pertenezcan a un detalle principal anterior', function (): void {
    $ambiente = Espacio::query()->create([
        'codigo' => 'AMB-DETALLE-VIEJO-01',
        'nombre' => 'Ambiente Detalle Viejo',
        'tipo' => TipoEspacio::AMBIENTE,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => false,
        'capacidad_personas' => 0,
    ]);

    $mesaPrincipal = Espacio::query()->create([
        'codigo' => 'MESA-DETALLE-VIEJO-01',
        'nombre' => 'Mesa Detalle Viejo 01',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);
    $mesaVieja = Espacio::query()->create([
        'codigo' => 'MESA-DETALLE-VIEJO-02',
        'nombre' => 'Mesa Detalle Viejo 02',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);
    $mesaNueva = Espacio::query()->create([
        'codigo' => 'MESA-DETALLE-NUEVO-03',
        'nombre' => 'Mesa Detalle Nuevo 03',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva limpia detalles viejos',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $mesaPrincipal->id,
        'fecha_check_in' => '2026-11-16',
        'hora_reserva' => '13:00',
        'duracion_horas' => 2,
        'adultos' => 4,
    ]);

    $principalActual = $reserva->detalles()->whereNull('parent_id')->firstOrFail();
    $principalViejo = $reserva->detalles()->create([
        'reservable_id' => $principalActual->reservable_id,
        'estado' => EstadoReservaDetalle::CONFIRMADO,
        'fecha_inicio' => '2026-11-16 13:00:00',
        'fecha_fin' => '2026-11-16 15:00:00',
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
    ]);
    $recursoViejo = app(ReservaRepositorioInterface::class)->resolverRecurso(TipoReserva::RESTAURANTE, (int) $mesaVieja->id);
    $reserva->detalles()->create([
        'parent_id' => $principalViejo->id,
        'reservable_id' => $recursoViejo->id,
        'estado' => EstadoReservaDetalle::CONFIRMADO,
        'fecha_inicio' => '2026-11-16 13:00:00',
        'fecha_fin' => '2026-11-16 15:00:00',
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
    ]);

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva, [
        'nombre_cliente' => 'Reserva limpia detalles viejos',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $mesaPrincipal->id,
        'fecha_check_in' => '2026-11-16',
        'hora_reserva' => '13:00',
        'duracion_horas' => 2,
        'adultos' => 6,
        'espacios_adicionales' => [
            ['espacio_id' => $mesaNueva->id, 'cantidad' => 1],
        ],
    ]);

    $idsMesasActivas = $actualizada->detalles()
        ->with('reservable.espacio')
        ->get()
        ->map(fn ($detalle): ?int => $detalle->reservable?->espacio?->id)
        ->filter()
        ->values()
        ->all();

    expect($idsMesasActivas)->toContain((int) $mesaPrincipal->id)
        ->and($idsMesasActivas)->toContain((int) $mesaNueva->id)
        ->and($idsMesasActivas)->not->toContain((int) $mesaVieja->id)
        ->and($actualizada->detalles()->whereNotNull('parent_id')->count())->toBe(1);
});

test('recalcula saldo respetando pagos existentes al editar', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $servicio = Servicio::query()
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva pagada parcial',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [], []);
    Cuenta::query()->where('reserva_id', $reserva->id)->delete();
    $reserva->update(['total_pagado' => 500, 'tipo_pago' => 'abono_50']);

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva->refresh(), [
        'nombre_cliente' => 'Reserva pagada parcial',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
        'servicios_adicionales' => [
            ['servicio_id' => $servicio->id, 'cantidad' => 1],
        ],
    ]);

    expect((float) $actualizada->total_pagado)->toBe(500.0)
        ->and((float) $actualizada->saldo)->toBe(round((float) $actualizada->total - 500, 2))
        ->and($actualizada->tipo_pago)->toBe(TipoPagoReserva::ABONO_50);
});

test('sincroniza la cuenta de reserva al agregar espacios adicionales en edición', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $espacio = Espacio::query()
        ->where('reservable', true)
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva con cuenta sincronizada',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [], []);
    Cuenta::query()->where('reserva_id', $reserva->id)->delete();

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-TEST-SYNC-'.$reserva->id,
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'reserva_id' => $reserva->id,
        'moneda_id' => $reserva->moneda_id,
        'subtotal' => $reserva->subtotal,
        'total' => $reserva->total,
        'total_pagado' => 0,
        'saldo' => $reserva->total,
        'abierta_at' => now(),
    ]);
    $cuenta->detalles()->create([
        'moneda_id' => $cuenta->moneda_id,
        'origen_type' => $reserva->getMorphClass(),
        'origen_id' => $reserva->id,
        'concepto' => "Reserva {$reserva->codigo_reserva}",
        'cantidad' => 1,
        'precio_unitario' => $reserva->subtotal,
        'subtotal' => $reserva->subtotal,
        'total' => $reserva->subtotal,
        'estado' => 1,
    ]);
    $cuenta->pagos()->create([
        'forma_pago' => MetodoPago::EFECTIVO,
        'moneda_id' => $cuenta->moneda_id,
        'estado' => EstadoPago::APLICADO,
        'monto' => round((float) $reserva->total * 0.5, 2),
        'propina' => 0,
    ]);
    $reserva->update([
        'tipo_pago' => TipoPagoReserva::ABONO_50,
        'total_pagado' => round((float) $reserva->total * 0.5, 2),
        'saldo' => round((float) $reserva->total * 0.5, 2),
    ]);
    $reserva = $reserva->refresh();

    $pagado = (float) $reserva->total_pagado;

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva, [
        'nombre_cliente' => 'Reserva con cuenta sincronizada',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
        'espacios_adicionales' => [
            ['espacio_id' => $espacio->id, 'cantidad' => 1],
        ],
    ]);

    $cuenta = $actualizada->cuentas()->firstOrFail();
    $detalleReserva = $cuenta->detalles()
        ->where('origen_type', $actualizada->getMorphClass())
        ->where('origen_id', $actualizada->id)
        ->firstOrFail();

    expect((float) $cuenta->refresh()->total)->toBe((float) $actualizada->total)
        ->and((float) $cuenta->total_pagado)->toBe($pagado)
        ->and((float) $actualizada->saldo)->toBe(round((float) $actualizada->total - $pagado, 2))
        ->and((float) $detalleReserva->subtotal)->toBe((float) $actualizada->subtotal)
        ->and($actualizada->detalles()->whereNotNull('parent_id')->count())->toBe(1);
});

test('persiste los acompañantes al editar', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva con acompañantes',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [], []);

    $acompanantes = [
        ['nombre' => 'Ana José', 'identificacion' => 'ID-100', 'tipo' => 'adulto'],
        ['nombre' => 'Luis José', 'tipo' => 'nino'],
    ];

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva, [
        'nombre_cliente' => 'Reserva con acompañantes',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
        'acompanantes' => $acompanantes,
    ]);

    expect($actualizada->acompanantes)->toBe($acompanantes);
});

test('no genera conflicto al conservar sus propios espacios adicionales', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $espacio = Espacio::query()
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva con espacio',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [], [
        ['espacio_id' => $espacio->id, 'cantidad' => 1],
    ]);

    $actualizada = app(ActualizarReserva::class)->ejecutar($reserva, [
        'nombre_cliente' => 'Reserva con espacio',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
        'espacios_adicionales' => [
            ['espacio_id' => $espacio->id, 'cantidad' => 1],
        ],
    ]);

    $hijos = $actualizada->detalles()
        ->with(['reservable.servicio', 'reservable.espacio'])
        ->get()
        ->whereNotNull('parent_id')
        ->values();

    expect($hijos)->toHaveCount(1)
        ->and($hijos->first()?->reservable?->espacio?->id)->toBe($espacio->id);
});

test('rechaza editar los recursos adicionales después del check-in', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $servicio = Servicio::query()
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva en uso',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [], []);

    $reserva->update(['estado' => EstadoReserva::CHECKED_IN]);

    expect(fn () => app(ActualizarReserva::class)->ejecutar($reserva, [
        'nombre_cliente' => 'Reserva en uso',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
        'servicios_adicionales' => [
            ['servicio_id' => $servicio->id, 'cantidad' => 1],
        ],
    ]))->toThrow(DomainException::class);
});
