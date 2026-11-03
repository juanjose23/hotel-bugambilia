<?php

declare(strict_types=1);

use App\BusinessLogic\Promociones\ValidarPromocionReserva;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Events\Reservas\ReservaCreada;
use App\Filament\Pages\Restaurante\GestionMesas;
use App\Filament\Resources\Restaurante\PedidoResource\Pages\ListPedidos;
use App\Interactors\Cuentas\Cobros\CobrarCuenta;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Facturacion\Stripe\MarcarPagoStripeFallido;
use App\Interactors\Restaurante\Cocina\MarcarTodosItemsServidos;
use App\Interactors\Restaurante\Mesas\ConfirmarLlegadaReservaMesa;
use App\Interactors\Restaurante\Pedidos\AplicarPagoPedidoStripe;
use App\Interactors\Restaurante\Pedidos\CancelarPedido;
use App\Interactors\Restaurante\Pedidos\CerrarPedidoMesa;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\ZonaDelivery;
use App\Repository\Models\User;
use App\Repository\Queries\Promociones\ObtenerPromocionesPublicasQuery;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Carbon::setTestNow('2027-01-01 10:00:00');
    Moneda::query()->firstOrCreate(['codigo' => 'NIO'], ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true, 'estado' => 1]);
    $restaurante = Espacio::query()->create(['codigo' => 'REST-TEST', 'nombre' => 'Restaurante', 'tipo' => TipoEspacio::RESTAURANTE, 'estado' => 1, 'web' => true, 'reservable' => true, 'capacidad_personas' => 40]);
    $this->mesa = Espacio::query()->create(['padre_id' => $restaurante->id, 'codigo' => 'M-TEST', 'nombre' => 'Mesa prueba', 'tipo' => TipoEspacio::MESA, 'estado' => 1, 'reservable' => true, 'capacidad_personas' => 4]);
    $producto = Producto::factory()->create();
    $activo = Activo::query()->create(['codigo_inventario' => 'AF-FLUJO-TEST', 'producto_id' => $producto->id, 'nombre_descriptivo' => 'Mesa de prueba', 'estado' => 1, 'fecha_adquisicion' => now()->toDateString()]);
    $this->mesa->asignacionesActivos()->create(['activo_id' => $activo->id, 'fecha_inicio' => now()->toDateString(), 'estado' => 1]);
    $this->plato = Plato::query()->create(['codigo' => 'PL-TEST', 'nombre' => 'Almuerzo', 'estado' => 1, 'web' => true, 'precio_base' => 120]);
    $this->plato->precios()->create(['moneda_id' => Moneda::query()->where('codigo', 'NIO')->value('id'), 'precio' => 120, 'fecha_inicio' => '2026-01-01', 'estado' => 1]);
    ZonaDelivery::query()->create(['codigo' => 'EST', 'nombre' => 'Estelí', 'activo' => true, 'costo_envio' => 50, 'municipios' => ['Estelí']]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('guarda la preorden pública con el precio del servidor', function (): void {
    Event::fake([ReservaCreada::class]);
    $respuesta = $this->postJson(route('restaurante.reservar-mesa'), ['nombre_cliente' => 'Ana Pérez', 'telefono_cliente' => '88889999', 'email_cliente' => 'ana@example.test', 'fecha' => '2027-01-03', 'hora' => '12:00', 'duracion_horas' => 2, 'adultos' => 2, 'espacio_id' => $this->mesa->id, 'items_preorden' => [['plato_id' => $this->plato->id, 'cantidad' => 2, 'precio_unitario' => 1]], 'tipo_pago_reserva' => 'sin_pago']);
    $respuesta->assertSuccessful()->assertJsonPath('success', true);
    $reserva = Reserva::query()->findOrFail($respuesta->json('reserva_id'));
    expect($reserva->ultimaEntradaBitacora('preorden')['items'][0]['precio_unitario'])->toEqual(120);
    expect($reserva->ultimaEntradaBitacora('preorden')['items'][0]['cantidad'])->toEqual(2);
});

it('rechaza horarios inválidos y platillos privados', function (): void {
    $this->getJson(route('restaurante.mesas-disponibles', ['fecha' => '2027-01-03', 'hora' => '99:99', 'comensales' => 2, 'duracion_horas' => 2]))->assertUnprocessable();
    $this->plato->update(['web' => false]);
    $this->postJson(route('restaurante.pedido'), ['nombre' => 'Ana Pérez', 'telefono' => '88889999', 'email' => 'ana@example.test', 'direccion' => 'Frente al parque central', 'departamento' => 'Estelí', 'municipio' => 'Estelí', 'metodo_pago' => 'efectivo', 'items' => [['plato_id' => $this->plato->id, 'cantidad' => 1]]])->assertUnprocessable();
    expect(Pedido::query()->count())->toBe(0);
});

it('incluye el envío en la cuenta y permite seguimiento al comprador invitado', function (): void {
    $respuesta = $this->postJson(route('restaurante.pedido'), ['nombre' => 'Ana Pérez', 'telefono' => '88889999', 'email' => 'ana@example.test', 'direccion' => 'Frente al parque central', 'departamento' => 'Estelí', 'municipio' => 'Estelí', 'metodo_pago' => 'efectivo', 'items' => [['plato_id' => $this->plato->id, 'cantidad' => 2]]]);
    $respuesta->assertSuccessful()->assertJsonPath('total', 333.5);
    $pedido = Pedido::query()->findOrFail($respuesta->json('pedido_id'));
    expect((float) $pedido->cuenta->total)->toBe(333.5)->and((float) $pedido->cuenta->total_pagado)->toBe(0.0);
    $this->get($respuesta->json('url_seguimiento'))->assertOk();
    $this->withSession(['pedidos_publicos' => []])->get($respuesta->json('url_seguimiento'))->assertForbidden();
});

it('aplica la promoción gastronómica en una reserva real y protege sus componentes', function (): void {
    Event::fake([ReservaCreada::class]);
    $promocion = Promocion::query()->create(['codigo' => 'MENU-PROMO', 'nombre' => 'Menú de prueba', 'web' => true, 'estado' => 1, 'fecha_inicio' => '2026-12-01', 'fecha_fin' => '2027-02-01', 'precio_paquete' => 100, 'descuento_porcentaje' => 10, 'personas_incluidas' => 2]);
    $promocion->items()->create(['item_type' => Plato::class, 'item_id' => $this->plato->id]);
    $this->get(route('promociones.reservar', $promocion->id))->assertOk();
    $respuesta = $this->postJson(route('promociones.reservar.store', $promocion->id), ['opcion' => 'restaurante:0', 'nombre_cliente' => 'Ana Pérez', 'telefono_cliente' => '88889999', 'email_cliente' => 'ana@example.test', 'fecha_check_in' => '2027-01-03', 'hora_reserva' => '12:00', 'duracion_horas' => 2, 'adultos' => 2, 'tipo_pago_reserva' => 'sin_pago']);
    $respuesta->assertCreated()->assertJsonPath('success', true);
    $reserva = Reserva::query()->findOrFail($respuesta->json('reserva_id'));
    expect($reserva->promocion_id)->toBe($promocion->id)->and((float) $reserva->total)->toBe(103.5);
    $this->postJson(route('restaurante.reservar-mesa'), ['nombre_cliente' => 'Otra persona', 'telefono_cliente' => '88889999', 'fecha' => '2027-01-04', 'hora' => '12:00', 'adultos' => 2, 'promocion_id' => $promocion->id, 'items_preorden' => [['plato_id' => $this->plato->id, 'cantidad' => 50]]])->assertUnprocessable();
});

it('oculta promociones privadas futuras y vencidas', function (): void {
    $base = ['nombre' => 'Oferta', 'web' => true, 'estado' => 1, 'fecha_inicio' => '2026-12-01', 'fecha_fin' => '2027-02-01'];
    $modelo = Promocion::class;
    $visible = $modelo::query()->create(array_merge($base, ['codigo' => 'VISIBLE']));
    $privada = $modelo::query()->create(array_merge($base, ['codigo' => 'PRIVADA', 'web' => false]));
    $futura = $modelo::query()->create(array_merge($base, ['codigo' => 'FUTURA', 'fecha_inicio' => '2027-01-02']));
    $vencida = $modelo::query()->create(array_merge($base, ['codigo' => 'VENCIDA', 'fecha_fin' => '2026-12-31']));
    expect(app(ObtenerPromocionesPublicasQuery::class)->ejecutar()->modelKeys())->toBe([$visible->id]);
    expect(app(ValidarPromocionReserva::class)->vigente($privada->id)->id)->toBe($privada->id);
    expect(fn () => app(ValidarPromocionReserva::class)->vigente($privada->id, publica: true))->toThrow(InvalidArgumentException::class);
    foreach ([$privada, $futura, $vencida] as $oferta) {
        $this->get(route('promociones.reservar', $oferta->id))->assertNotFound();
    }
});

it('conserva la cuenta y el abono al llegar sin duplicar el menú reservado', function (): void {
    Event::fake([ReservaCreada::class]);
    $respuesta = $this->postJson(route('restaurante.reservar-mesa'), ['nombre_cliente' => 'Ana Pérez', 'telefono_cliente' => '88889999', 'fecha' => '2027-01-03', 'hora' => '12:00', 'adultos' => 2, 'items_preorden' => [['plato_id' => $this->plato->id, 'cantidad' => 2]]])->assertCreated();
    $reserva = Reserva::query()->findOrFail($respuesta->json('reserva_id'));
    $cuenta = $reserva->cuentas()->firstOrFail();
    app(RegistrarPagoCuenta::class)->ejecutar($cuenta, MetodoPago::TRANSFERENCIA, 100, referenciaTransaccion: 'ABONO-TEST');
    $total = (float) $cuenta->refresh()->total;
    $pagos = PagoCuenta::query()->count();
    Carbon::setTestNow('2027-01-03 11:45:00');
    $pedido = app(ConfirmarLlegadaReservaMesa::class)->ejecutar($this->mesa->id);
    expect($pedido->cuenta_id)->toBe($cuenta->id)->and($pedido->items)->toHaveCount(1)->and((float) $cuenta->refresh()->total)->toBe($total)->and(PagoCuenta::query()->count())->toBe($pagos);
    expect(fn () => app(ConfirmarLlegadaReservaMesa::class)->ejecutar($this->mesa->id))->toThrow(DomainException::class);
    app(CerrarPedidoMesa::class)->ejecutar($pedido, metodoPago: MetodoPago::TRANSFERENCIA, montoRecibido: $total - 100);
    expect((float) $cuenta->refresh()->saldo)->toBe(0.0)->and(PagoCuenta::query()->count())->toBe($pagos + 1);
});

it('no envía a cocina ni registra cobro antes de confirmar Stripe y confirma una sola vez', function (): void {
    config(['services.stripe.key' => 'pk_test_ficticia', 'services.stripe.secret' => 'sk_test_ficticia', 'services.stripe.webhook_secret' => 'whsec_test_ficticia']);
    Http::preventStrayRequests();
    Http::fake(fn ($request) => Http::response(str_contains($request->url(), '/payment_intents') ? ['id' => 'pi_prueba_pedido', 'client_secret' => 'pi_prueba_secret', 'status' => 'requires_payment_method'] : ['id' => 'cus_prueba', 'data' => []]));
    $respuesta = $this->postJson(route('restaurante.pedido'), ['nombre' => 'Ana Pérez', 'telefono' => '88889999', 'email' => 'ana@example.test', 'direccion' => 'Frente al parque central', 'departamento' => 'Estelí', 'municipio' => 'Estelí', 'metodo_pago' => 'stripe', 'items' => [['plato_id' => $this->plato->id, 'cantidad' => 2]]])->assertCreated();
    $pedido = Pedido::query()->findOrFail($respuesta->json('pedido_id'));
    expect($pedido->estado)->toBe(EstadoPedido::ABIERTO)->and((float) $pedido->cuenta->total_pagado)->toBe(0.0);
    $intent = ['id' => 'pi_prueba_pedido', 'status' => 'succeeded', 'currency' => 'nio', 'amount_received' => 33350, 'metadata' => ['pedido_id' => (string) $pedido->id]];
    $aplicar = app(AplicarPagoPedidoStripe::class);
    expect(fn () => $aplicar->ejecutar($pedido->id, 'pi_prueba_pedido', array_merge($intent, ['amount_received' => 1])))->toThrow(DomainException::class);
    expect(fn () => $aplicar->ejecutar($pedido->id, 'pi_prueba_pedido', array_merge($intent, ['metadata' => ['pedido_id' => '99999']])))->toThrow(DomainException::class);
    $aplicar->ejecutar($pedido->id, 'pi_prueba_pedido', $intent);
    expect(app(MarcarPagoStripeFallido::class)->ejecutar('pi_prueba_pedido', ['type' => 'payment_intent.payment_failed']))->toBeNull();
    $aplicar->ejecutar($pedido->id, 'pi_prueba_pedido', $intent);
    expect($pedido->refresh()->estado)->toBe(EstadoPedido::EN_PREPARACION)->and((float) $pedido->cuenta->refresh()->saldo)->toBe(0.0)->and(PagoCuenta::query()->where('cuenta_id', $pedido->cuenta_id)->count())->toBe(1);
    $pedido->items()->update(['estado' => EstadoItemPedido::LISTO]);
    app(MarcarTodosItemsServidos::class)->ejecutar($pedido->id);
    $usuario = User::factory()->create(['is_admin' => true]);
    $usuario->assignRole(Role::findOrCreate('super_admin', 'web'));
    $this->actingAs($usuario);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Livewire::test(ListPedidos::class)
        ->assertActionVisible(TestAction::make('finalizarPedidoPagado')->table($pedido))
        ->callAction(TestAction::make('finalizarPedidoPagado')->table($pedido))
        ->assertHasNoFormErrors();
    expect($pedido->refresh()->estado)->toBe(EstadoPedido::PAGADO)->and(PagoCuenta::query()->where('cuenta_id', $pedido->cuenta_id)->count())->toBe(1)->and($pedido->cuenta->ventas()->count())->toBe(1);
});

it('anula también el envío al cancelar un delivery sin pagos', function (): void {
    $respuesta = $this->postJson(route('restaurante.pedido'), ['nombre' => 'Ana Pérez', 'telefono' => '88889999', 'email' => 'ana@example.test', 'direccion' => 'Frente al parque central', 'departamento' => 'Estelí', 'municipio' => 'Estelí', 'metodo_pago' => 'efectivo', 'items' => [['plato_id' => $this->plato->id, 'cantidad' => 1]]])->assertCreated();
    $pedido = Pedido::query()->findOrFail($respuesta->json('pedido_id'));
    app(CancelarPedido::class)->ejecutar($pedido);
    expect($pedido->refresh()->estado)->toBe(EstadoPedido::CANCELADO)->and((float) $pedido->cuenta->refresh()->saldo)->toBe(0.0);
});

it('rechaza reservas solapadas y grupos que no caben en las mesas compatibles', function (): void {
    Event::fake([ReservaCreada::class]);
    $datos = ['nombre_cliente' => 'Ana Pérez', 'telefono_cliente' => '88889999', 'fecha' => '2027-01-03', 'hora' => '12:00', 'duracion_horas' => 2, 'adultos' => 2, 'espacio_id' => $this->mesa->id];
    $this->postJson(route('restaurante.reservar-mesa'), $datos)->assertCreated();
    $this->postJson(route('restaurante.reservar-mesa'), $datos)->assertUnprocessable();
    $this->postJson(route('restaurante.reservar-mesa'), array_merge($datos, ['fecha' => '2027-01-04', 'adultos' => 30]))->assertUnprocessable();
    expect(Reserva::query()->count())->toBe(1);
});

it('el cobro utilizado por caja finaliza el delivery servido y genera una sola venta', function (): void {
    $respuesta = $this->postJson(route('restaurante.pedido'), ['nombre' => 'Ana Pérez', 'telefono' => '88889999', 'email' => 'ana@example.test', 'direccion' => 'Frente al parque central', 'departamento' => 'Estelí', 'municipio' => 'Estelí', 'metodo_pago' => 'efectivo', 'items' => [['plato_id' => $this->plato->id, 'cantidad' => 1]]])->assertCreated();
    $pedido = Pedido::query()->findOrFail($respuesta->json('pedido_id'));
    $pedido->items()->update(['estado' => EstadoItemPedido::LISTO]);
    app(MarcarTodosItemsServidos::class)->ejecutar($pedido->id);
    $cuenta = $pedido->cuenta;
    $resultado = app(CobrarCuenta::class)->ejecutar($cuenta, null, ['forma_pago' => MetodoPago::TRANSFERENCIA->value, 'monto' => $cuenta->saldo, 'moneda_pago_id' => $cuenta->moneda_id, 'tipo_comprobante' => 'voucher']);
    expect($resultado['cerrada'])->toBeTrue()->and($pedido->refresh()->estado)->toBe(EstadoPedido::PAGADO)->and($cuenta->ventas()->count())->toBe(1);
    expect(fn () => app(CerrarPedidoMesa::class)->ejecutar($pedido))->toThrow(DomainException::class);
    expect($cuenta->ventas()->count())->toBe(1)->and($cuenta->pagos()->count())->toBe(1);
});

it('permite finalizar desde gestión de mesas una atención servida que ya está pagada', function (): void {
    $respuesta = $this->postJson(route('restaurante.pedido'), ['nombre' => 'Ana Pérez', 'telefono' => '88889999', 'email' => 'ana@example.test', 'direccion' => 'Frente al parque central', 'departamento' => 'Estelí', 'municipio' => 'Estelí', 'metodo_pago' => 'efectivo', 'items' => [['plato_id' => $this->plato->id, 'cantidad' => 1]]])->assertCreated();
    $pedido = Pedido::query()->findOrFail($respuesta->json('pedido_id'));
    app(RegistrarPagoCuenta::class)->ejecutar($pedido->cuenta, MetodoPago::TRANSFERENCIA, (float) $pedido->cuenta->saldo);
    $pedido->items()->update(['estado' => EstadoItemPedido::LISTO]);
    app(MarcarTodosItemsServidos::class)->ejecutar($pedido->id);
    $usuario = User::factory()->create(['is_admin' => true]);
    $usuario->assignRole(Role::findOrCreate('super_admin', 'web'));
    $this->actingAs($usuario);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Livewire::test(GestionMesas::class)->set('pedidoCobroId', $pedido->id)->callAction('finalizarPedidoPagado')->assertHasNoFormErrors();
    expect($pedido->refresh()->estado)->toBe(EstadoPedido::PAGADO)->and($pedido->cuenta->ventas()->count())->toBe(1)->and($pedido->cuenta->pagos()->count())->toBe(1);
});
