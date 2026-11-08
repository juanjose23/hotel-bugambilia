<?php

declare(strict_types=1);

namespace Tests\Feature\Reservas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('los botones de pago en checkout pueden montarse sin errores', function (): void {
    $user = User::factory()->create();
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $moneda = Moneda::firstOrCreate(
        ['codigo' => 'NIO'],
        ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'tasa_cambio' => 1.0, 'activo' => true]
    );

    $persona = Persona::factory()->create(['primer_nombre' => 'Ana']);
    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Martínez',
        'fecha_nacimiento' => '1992-05-10',
    ]);
    $tipoCat = CatalogoTipo::firstOrCreate(['codigo' => 'tipo_cliente'], ['nombre' => 'Tipo Cliente', 'estado' => 1]);
    $cat = Catalogo::firstOrCreate(['codigo' => 'NATURAL', 'catalogo_tipo_id' => $tipoCat->id], ['nombre' => 'Natural', 'estado' => 1]);

    $cliente = Cliente::create([
        'persona_id' => $persona->id,
        'catalogo_id' => $cat->id,
        'codigo_cliente' => 'CLI-TEST-002',
        'estado' => EstadoGeneral::Activo,
    ]);

    $recurso = RecursoReservable::create([
        'nombre' => 'Habitación 201 Reservable',
        'codigo' => 'REC-201',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
        'capacidad' => 2,
        'estado' => EstadoRecursoReservable::ACTIVO,
    ]);

    $habitacion = Habitacion::factory()->create([
        'numero' => '201',
        'nombre' => 'Suite 201',
        'reservable_id' => $recurso->id,
    ]);

    $reserva = Reserva::create([
        'codigo_reserva' => 'RES-TEST-MOUNT',
        'cliente_id' => $cliente->id,
        'nombre_cliente' => 'Ana Martínez',
        'habitacion_id' => $habitacion->id,
        'moneda_id' => $moneda->id,
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CHECKED_IN,
        'tipo_pago' => TipoPagoReserva::ABONO_50,
        'fecha_check_in' => now()->subDay(),
        'fecha_check_out' => now(),
        'subtotal' => 300.0,
        'total' => 300.0,
        'total_pagado' => 0.0,
        'saldo' => 300.0,
        'adultos' => 1,
        'ninos' => 0,
    ]);

    $detalle = ReservaDetalle::create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now(),
        'cantidad_adultos' => 1,
        'cantidad_ninos' => 0,
        'precio_unitario' => 300.0,
        'subtotal' => 300.0,
        'impuestos' => 0.0,
        'total' => 300.0,
        'estado' => EstadoReservaDetalle::EN_USO,
    ]);

    $estancia = Estancia::create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacion->id,
        'usuario_check_in_id' => $user->id,
        'check_in_at' => now()->subDay(),
        'fecha_entrada_programada' => now()->subDay(),
        'fecha_salida_programada' => now(),
        'cantidad_llaves' => 1,
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    $cuenta = Cuenta::create([
        'numero_cuenta' => 'CTA-TEST-MOUNT',
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
        'total' => 300.0,
        'total_pagado' => 0.0,
        'saldo' => 300.0,
    ]);

    $test = Livewire::test(CheckOutPage::class, ['record' => $reserva->id])
        ->assertSuccessful()
        ->assertActionVisible('registrar_pago')
        ->assertActionVisible('registrar_pagos_multiples')
        ->assertActionVisible('pagar_stripe');

    $test->mountAction('registrar_pago')
        ->assertHasNoErrors();

    $test->mountAction('registrar_pagos_multiples')
        ->assertHasNoErrors();

    $test->mountAction('pagar_stripe')
        ->assertHasNoErrors();
});
