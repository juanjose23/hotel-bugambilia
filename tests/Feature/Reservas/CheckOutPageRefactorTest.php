<?php

declare(strict_types=1);

namespace Tests\Feature\Reservas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
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

test('CheckOutPage can mount and resolve active stay from reserva ID without errors', function (): void {
    $user = User::factory()->create();
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $moneda = Moneda::firstOrCreate(
        ['codigo' => 'NIO'],
        [
            'nombre' => 'Córdoba nicaragüense',
            'simbolo' => 'C$',
            'tasa_cambio' => 1.0,
            'activo' => true,
        ]
    );

    $persona = Persona::factory()->create([
        'primer_nombre' => 'María',
    ]);
    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Gómez',
        'fecha_nacimiento' => '1990-01-01',
    ]);
    $tipoCatalogo = CatalogoTipo::firstOrCreate(
        ['codigo' => 'tipo_cliente'],
        ['nombre' => 'Tipo de Cliente'],
    );
    $catalogo = Catalogo::firstOrCreate(
        ['codigo' => 'cliente_regular'],
        [
            'nombre' => 'Cliente Regular',
            'catalogo_tipo_id' => $tipoCatalogo->id,
        ],
    );

    $cliente = Cliente::create([
        'tipo_cliente' => 'natural',
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'activo' => true,
    ]);

    $recurso = RecursoReservable::create([
        'nombre' => 'Habitación 101 Reservable',
        'codigo' => 'REC-101',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
        'capacidad' => 2,
        'estado' => EstadoRecursoReservable::ACTIVO,
    ]);

    $habitacion = Habitacion::factory()->create([
        'numero' => '101',
        'nombre' => 'Habitación Estándar 101',
        'estado' => EstadoEspacio::Ocupado,
        'reservable_id' => $recurso->id,
    ]);

    $reserva = Reserva::create([
        'codigo_reserva' => 'RES-TEST-001',
        'cliente_id' => $cliente->id,
        'nombre_cliente' => 'María Gómez',
        'email_cliente' => 'maria@test.com',
        'tipo_reserva' => TipoReserva::HABITACION,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => now()->subDays(2)->format('Y-m-d'),
        'fecha_check_out' => now()->format('Y-m-d'),
        'adultos' => 2,
        'ninos' => 0,
        'estado' => EstadoReserva::CHECKED_IN,
        'subtotal' => 200.0,
        'descuento' => 0.0,
        'total' => 200.0,
        'total_pagado' => 200.0,
        'saldo' => 0.0,
        'moneda_id' => $moneda->id,
        'tipo_pago' => TipoPagoReserva::PAGO_COMPLETO,
    ]);

    $detalle = ReservaDetalle::create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now()->subDays(2),
        'fecha_fin' => now(),
        'cantidad_adultos' => 2,
        'cantidad_ninos' => 0,
        'precio_unitario' => 100.0,
        'subtotal' => 200.0,
        'impuestos' => 0.0,
        'total' => 200.0,
        'estado' => EstadoReservaDetalle::EN_USO,
    ]);

    $estancia = Estancia::create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacion->id,
        'usuario_check_in_id' => $user->id,
        'check_in_at' => now()->subDays(2),
        'fecha_entrada_programada' => now()->subDays(2),
        'fecha_salida_programada' => now(),
        'cantidad_llaves' => 2,
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    $cuenta = Cuenta::create([
        'numero_cuenta' => 'CTA-TEST-001',
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
        'total' => 200.0,
        'total_pagado' => 200.0,
        'saldo' => 0.0,
    ]);

    Livewire::test(CheckOutPage::class, ['record' => $reserva->id])
        ->assertSuccessful()
        ->assertSee('Check-Out: 101 - Habitación Estándar 101')
        ->assertSee('CTA-TEST-001')
        ->set('data.consumos_revisados', true)
        ->set('data.llaves_devueltas', 2)
        ->call('submit')
        ->assertHasNoErrors();

    expect($estancia->fresh()->estado)->toBe(EstadoEstancia::FINALIZADA)
        ->and($habitacion->fresh()->estado)->toBe(EstadoEspacio::Sucio)
        ->and($reserva->fresh()->estado)->toBe(EstadoReserva::CHECKED_OUT);
});
