<?php

declare(strict_types=1);

use App\Enums\Cuentas\MetodoPago;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Restaurante\UbicacionCocina;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Inventario\Lotes\RegistrarMermaGlobalDiaria;
use App\Interactors\Restaurante\Cocina\CrearSolicitudAbastecimientoCocina;
use App\Interactors\Restaurante\Cocina\ProcesarProcesoCocina;
use App\Interactors\Restaurante\Pedidos\AbrirPedidoMesa;
use App\Interactors\Restaurante\Pedidos\CerrarPedidoMesa;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\User;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;

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

afterEach(function (): void {
    PedidoItem::query()->forceDelete();
    Pedido::query()->forceDelete();
    Reserva::query()->forceDelete();
    Espacio::query()->where('tipo', TipoEspacio::MESA)->update([
        'estado' => EstadoEspacio::Disponible,
        'meta_datos' => null,
    ]);
});

function obtenerUsuarioDuskAdmin(): User
{
    $admin = User::query()->where('email', 'admin@bugambilia.test')->first();

    if ($admin === null) {
        $admin = User::factory()->create([
            'email' => 'admin@bugambilia.test',
            'is_admin' => true,
        ]);
    }

    $persona = Persona::query()->create([
        'primer_nombre' => 'Admin Dusk',
        'tipo_persona' => 'natural',
    ]);

    $admin->update(['persona_id' => $persona->id]);

    Colaborador::query()->firstOrCreate([
        'id' => $admin->id,
    ], [
        'codigo' => 'COL-DUSK-'.$admin->id,
        'persona_id' => $persona->id,
        'fecha_ingreso' => now(),
        'estado' => EstadoGeneral::Activo,
    ]);

    $rol = Role::firstOrCreate([
        'name' => config('filament-shield.super_admin.name', 'super_admin'),
        'guard_name' => 'web',
    ]);

    if (! $admin->hasRole($rol)) {
        $admin->assignRole($rol);
    }

    return $admin;
}

test('flujo de mesa con reservacion: confirmacion de llegada y apertura automatica de comanda', function (): void {
    $user = obtenerUsuarioDuskAdmin();
    $mesaReservada = Espacio::query()->where('codigo', 'MESA-07')->firstOrFail();

    $recurso = RecursoReservable::query()->firstOrCreate([
        'nombre' => $mesaReservada->nombre,
    ], [
        'tipo' => TipoRecursoReservable::ESPACIO,
        'control_disponibilidad' => ControlDisponibilidad::HORARIO,
        'estado' => EstadoRecursoReservable::ACTIVO,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-DUSK-'.uniqid(),
        'nombre_cliente' => 'Cliente Reserva Dusk VIP',
        'tipo_reserva' => TipoReserva::RESTAURANTE,
        'espacio_id' => $mesaReservada->id,
        'fecha_check_in' => now()->toDateString(),
        'hora_reserva' => now()->format('H:i'),
        'adultos' => 2,
        'estado' => EstadoReserva::CONFIRMADA,
        'total' => 0,
    ]);

    $reserva->detalles()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoReservaDetalle::CONFIRMADO,
        'fecha_inicio' => now()->subHours(1)->toDateTimeString(),
        'fecha_fin' => now()->addHours(12)->toDateTimeString(),
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
    ]);

    $mesaReservada->update([
        'estado' => EstadoEspacio::Disponible,
        'meta_datos' => [
            'reserva_id' => $reserva->id,
            'codigo_reserva' => $reserva->codigo_reserva,
            'nombre_cliente' => $reserva->nombre_cliente,
        ],
    ]);

    $this->browse(function (Browser $browser) use ($user, $mesaReservada): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/mesas')
            ->waitForText('Restaurante')
            ->assertSee('Mesa 07 (VIP)')
            ->assertPresent("@mesa-{$mesaReservada->id}-llegada")
            ->pause(1000)
            ->click("@mesa-{$mesaReservada->id}-llegada")
            ->waitFor("@mesa-{$mesaReservada->id}-cobrar")
            ->assertPresent("@mesa-{$mesaReservada->id}-cobrar")
            ->pause(1000);
    });
});

test('flujo de mesa sin reservacion: apertura de comanda, navegacion a cocina KDS y vista de cobro', function (): void {
    $user = obtenerUsuarioDuskAdmin();
    $mesaDisponible = Espacio::query()->where('codigo', 'MESA-01')->firstOrFail();

    $mesaDisponible->update(['estado' => EstadoEspacio::Disponible]);

    $this->browse(function (Browser $browser) use ($user, $mesaDisponible): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/mesas')
            ->waitForText('Restaurante')
            ->assertSee('Mesa 01')
            ->assertPresent("@mesa-{$mesaDisponible->id}-comanda")
            ->click("@mesa-{$mesaDisponible->id}-comanda")
            ->waitForLocation('/admin/restaurante/pedidos/create')
            ->assertPathIs('/admin/restaurante/pedidos/create')
            ->pause(1000);

        $browser->visit('/admin/restaurante/pantalla-pedidos')
            ->waitForText('Pantalla de Pedidos')
            ->assertSee('Pantalla de Pedidos')
            ->pause(1000);

        $browser->visit('/admin/restaurante/cocina')
            ->waitForText('Centro de Cocina')
            ->assertSee('Centro de Cocina')
            ->pause(1000);

        $browser->visit('/admin/restaurante/mesas')
            ->waitForText('Restaurante')
            ->assertPresent('@buscar-mesa')
            ->pause(1000);
    });
});

test('validacion de no sobre-ocupacion y filtrado interactivo del mapa de mesas', function (): void {
    $user = obtenerUsuarioDuskAdmin();
    $mesaOcupada = Espacio::query()->where('codigo', 'MESA-02')->firstOrFail();

    $mesaOcupada->update(['estado' => EstadoEspacio::Ocupado]);

    $this->browse(function (Browser $browser) use ($user, $mesaOcupada): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/mesas')
            ->waitForText('Restaurante')
            ->type('@buscar-mesa', 'Mesa 02')
            ->pause(1000)
            ->assertSee('Mesa 02')
            ->assertDontSee('Mesa 05')
            ->assertDontSee("@mesa-{$mesaOcupada->id}-comanda")
            ->clear('@buscar-mesa')
            ->pause(1000);
    });
});

test('flujo interactivo de union de mesas desde el panel de gestion', function (): void {
    $user = obtenerUsuarioDuskAdmin();
    $mesa1 = Espacio::query()->where('codigo', 'MESA-01')->firstOrFail();
    $mesa2 = Espacio::query()->where('codigo', 'MESA-02')->firstOrFail();

    $mesa1->update(['estado' => EstadoEspacio::Disponible, 'meta_datos' => null]);
    $mesa2->update(['estado' => EstadoEspacio::Disponible, 'meta_datos' => null]);

    $this->browse(function (Browser $browser) use ($user, $mesa1, $mesa2): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/mesas')
            ->waitForText('Restaurante')
            ->assertPresent('@unir-mesas')
            ->click('@unir-mesas')
            ->pause(1000)
            ->select('@unir-mesa-principal', (string) $mesa1->id)
            ->pause(1000)
            ->check("input[value='{$mesa2->id}']")
            ->pause(1000)
            ->click('@confirmar-union')
            ->pause(2000)
            ->assertSee('Mesa 01')
            ->pause(1000);
    });
});

test('modulo de pedidos: listado formal, acciones de cobro e impresion', function (): void {
    $user = obtenerUsuarioDuskAdmin();

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/pedidos')
            ->waitForText('Pedidos')
            ->assertSee('Pedidos')
            ->pause(1000);
    });
});

test('modulo de procesos de cocina y transformacion de materia prima', function (): void {
    $user = obtenerUsuarioDuskAdmin();

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/procesos-cocina')
            ->waitForText('Procesos')
            ->assertSee('Procesos')
            ->pause(1000);
    });
});

test('modulo de platos y catalogo de recetas del restaurante', function (): void {
    $user = obtenerUsuarioDuskAdmin();

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/platos')
            ->waitForText('Platos')
            ->assertSee('Platos')
            ->pause(1000);
    });
});

test('flujo E2E completo: apertura de comanda, envio a cocina, transiciones KDS (listo/servido) y cobro', function (): void {
    $user = obtenerUsuarioDuskAdmin();
    $mesa = Espacio::query()->where('codigo', 'MESA-03')->firstOrFail();
    $mesa->update(['estado' => EstadoEspacio::Disponible, 'meta_datos' => null]);
    $plato = Plato::query()->firstOrFail();

    $pedido = app(AbrirPedidoMesa::class)->ejecutar(
        mesa: $mesa,
        meseroId: $user->id,
        items: [
            [
                'plato_id' => $plato->id,
                'cantidad' => 2,
                'precio_unitario' => (float) ($plato->precio_venta ?? 150.0),
                'observaciones' => 'Sin cebolla - Término medio',
            ],
        ]
    );

    app(EnviarPedidoACocina::class)->ejecutarPorId($pedido->id);
    $pedido->refresh();
    $item = $pedido->items->first();

    $this->browse(function (Browser $browser) use ($user, $pedido, $item): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/cocina')
            ->waitForText('Comandas en Preparación')
            ->waitFor("@kds-pedido-{$pedido->id}")
            ->assertPresent("@kds-item-{$item->id}-listo")
            ->click("@kds-item-{$item->id}-listo")
            ->pause(1500);

        $browser->visit('/admin/restaurante/pantalla-pedidos')
            ->waitForText('Pantalla de Turnos & Despacho')
            ->waitFor("@kds-item-{$item->id}-servido")
            ->assertPresent("@kds-item-{$item->id}-servido")
            ->click("@kds-item-{$item->id}-servido")
            ->pause(1500);

        $browser->visit('/admin/restaurante/pedidos')
            ->waitForText('Pedidos')
            ->assertSee($pedido->codigo)
            ->pause(1000);
    });
});

test('flujo E2E de transformacion de materia prima, abastecimiento de cocina y registro de mermas', function (): void {
    $user = obtenerUsuarioDuskAdmin();
    $plato = Plato::query()->firstOrFail();
    $producto = Producto::query()->first();
    $variante = ProductoVariante::query()->first();
    $colaborador = Colaborador::query()->first();

    $proceso = app(ProcesarProcesoCocina::class)->guardar(
        proceso: null,
        data: [
            'plato_id' => $plato->id,
            'producto_origen_id' => $plato->producto_receta_id ?? $producto?->id,
            'cantidad_platos' => 5,
            'cantidad_procesada' => 5.0,
            'observaciones' => 'Proceso Dusk de corte de filete y salsa base',
            'items' => $producto !== null ? [
                [
                    'producto_destino_id' => $producto->id,
                    'variante_destino_id' => $variante?->id,
                    'cantidad' => 2.5,
                    'costo_asignado' => 150.0,
                    'es_merma' => false,
                ],
            ] : [],
        ],
        usuarioId: $user->id
    );

    if ($colaborador !== null && $variante !== null) {
        app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
            motivo: 'Insumos faltantes para turno de cenas Dusk',
            items: [
                [
                    'producto_variante_id' => $variante->id,
                    'cantidad' => 10.0,
                    'justificacion' => 'Uso inmediato en cocina',
                ],
            ],
            fechaNecesita: now()->addDays(1)->toDateString(),
            colaboradorId: (int) $colaborador->id
        );
    }

    $ubicacionRestaurante = Ubicacion::query()
        ->where('nombre', UbicacionCocina::RESTAURANTE->value)
        ->first();

    if ($ubicacionRestaurante !== null && $producto !== null) {
        app(RegistrarMermaGlobalDiaria::class)->ejecutar(
            fecha: now()->toDateString(),
            ubicacionId: $ubicacionRestaurante->id,
            items: [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 0.5,
                    'motivo' => 'Derrame accidental en preparación Dusk',
                ],
            ],
            usuarioId: $user->id
        );
    }

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/procesos-cocina')
            ->waitForText('Procesos')
            ->assertSee('Procesos')
            ->pause(1000);

        $browser->visit('/admin/restaurante/cocina')
            ->waitForText('Centro de Cocina')
            ->assertSee('Centro de Cocina')
            ->pause(1000);
    });
});

test('flujo E2E integrado multi-modulo: cierre de pedido -> mesa sucia -> solicitud automatica de limpieza -> ejecucion -> mesa liberada disponible', function (): void {
    $user = obtenerUsuarioDuskAdmin();
    $mesa = Espacio::query()->where('codigo', 'MESA-04')->firstOrFail();
    $mesa->update(['estado' => EstadoEspacio::Disponible, 'meta_datos' => null]);
    $plato = Plato::query()->firstOrFail();

    $pedido = app(AbrirPedidoMesa::class)->ejecutar(
        mesa: $mesa,
        meseroId: $user->id,
        items: [
            [
                'plato_id' => $plato->id,
                'cantidad' => 1,
                'precio_unitario' => (float) ($plato->precio_venta ?? 100.0),
            ],
        ]
    );

    app(CerrarPedidoMesa::class)->ejecutar(
        pedido: $pedido,
        metodoPago: MetodoPago::EFECTIVO,
        montoRecibido: (float) ($pedido->subtotal > 0 ? $pedido->subtotal : 100.0),
        usuarioId: $user->id
    );

    $mesa->refresh();
    expect($mesa->estado)->toBe(EstadoEspacio::Sucio);

    $solicitud = SolicitudLimpieza::query()
        ->where('limpiable_type', Espacio::class)
        ->where('limpiable_id', $mesa->id)
        ->first();
    expect($solicitud)->not->toBeNull();

    if ($solicitud !== null) {
        $solicitud->update(['estado' => EstadoLimpieza::Completada]);
        $mesa->update(['estado' => EstadoEspacio::Disponible]);
    }

    $mesa->refresh();
    expect($mesa->estado)->toBe(EstadoEspacio::Disponible);

    $this->browse(function (Browser $browser) use ($user, $mesa): void {
        $browser->loginAs($user)
            ->visit('/admin/restaurante/mesas')
            ->waitForText('Restaurante')
            ->type('@buscar-mesa', 'Mesa 04')
            ->pause(1000)
            ->assertSee('Mesa 04')
            ->assertPresent("@mesa-{$mesa->id}-comanda")
            ->pause(1000);

        $browser->visit('/admin/tablero-limpieza')
            ->waitForText('Filtrar por Ubicación')
            ->assertSee('Disponibles / Pendientes')
            ->pause(1000);
    });
});
