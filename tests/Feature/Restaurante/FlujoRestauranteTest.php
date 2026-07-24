<?php

declare(strict_types=1);

use App\BusinessLogic\Restaurante\ValidarDisponibilidadIngredientes;
use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\AbrirPedidoMesa;
use App\Interactors\Restaurante\CerrarPedidoMesa;
use App\Interactors\Restaurante\ConsumirIngredientesPedido;
use App\Interactors\Restaurante\EnviarPedidoACocina;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\ObtenerIngredientesPedidoQuery;

test('flujo completo de restaurante: abrir mesa ocupada, enviar a cocina y cerrar cargando a estancia con solicitud de limpieza', function (): void {
    // 1. Crear Mesa Disponible
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-05',
        'nombre' => 'Mesa Terraza 05',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
        'activo' => true,
    ]);

    // 2. Abrir Pedido en la Mesa
    $abrirInteractor = new AbrirPedidoMesa;
    $pedido = $abrirInteractor->ejecutar($mesa);

    expect($pedido->estado)->toBe(EstadoPedido::ABIERTO)
        ->and($mesa->refresh()->estado)->toBe(EstadoEspacio::Ocupado);

    // 3. Crear Platillo y agregar a la comanda
    $plato = Plato::query()->create([
        'codigo' => 'PLT-HAMB',
        'nombre' => 'Hamburguesa Bugambilias',
        'estado' => 1,
    ]);

    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => $plato->id,
        'cantidad' => 2,
        'precio_unitario' => 180.00,
        'subtotal' => 360.00,
        'estado' => EstadoItemPedido::PENDIENTE,
    ]);

    // 4. Enviar a Cocina (Mock de insumos para aislamiento)
    $ingredientesQuery = Mockery::mock(ObtenerIngredientesPedidoQuery::class);
    $ingredientesQuery->shouldReceive('ejecutar')->andReturn(null);

    $validarDisponibilidad = new ValidarDisponibilidadIngredientes;
    $repositorio = Mockery::mock(RestauranteRepositorioInterface::class);

    $consumirIngredientes = new ConsumirIngredientesPedido(
        $ingredientesQuery,
        $validarDisponibilidad,
        $repositorio
    );

    $enviarCocina = new EnviarPedidoACocina($consumirIngredientes);
    $pedidoEnviado = $enviarCocina->ejecutar($pedido);

    expect($pedidoEnviado->estado)->toBe(EstadoPedido::EN_PREPARACION)
        ->and($item->refresh()->estado)->toBe(EstadoItemPedido::EN_PREPARACION);

    // 5. Crear Estancia y Cuenta del Huésped
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-REST-100',
        'nombre_cliente' => 'Huésped Restaurante',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::ACTIVA,
        'check_in_at' => now(),
    ]);

    $cuenta = CuentaEstancia::query()->create([
        'estancia_id' => $estancia->id,
        'numero_folio' => 'CTA-REST-001',
        'estado' => EstadoCuentaEstancia::ABIERTA,
        'abierta_at' => now(),
    ]);

    // 6. Cerrar Pedido cargando a la Cuenta de Estancia
    $cerrarInteractor = new CerrarPedidoMesa;
    $pedidoCerrado = $cerrarInteractor->ejecutar(
        pedido: $pedido,
        cargarAHabitacion: true,
        cuentaEstancia: $cuenta
    );

    expect($pedidoCerrado->estado)->toBe(EstadoPedido::CARGADO_A_HABITACION)
        ->and((float) $pedidoCerrado->total)->toBe(360.00)
        ->and((float) $cuenta->refresh()->saldo)->toBe(360.00)
        ->and($mesa->refresh()->estado)->toBe(EstadoEspacio::Sucio);

    // 7. Verificar que se haya generado automáticamente la Solicitud de Limpieza para la mesa
    $solicitudLimpieza = SolicitudLimpieza::query()
        ->where('limpiable_type', $mesa->getMorphClass())
        ->where('limpiable_id', $mesa->id)
        ->first();

    expect($solicitudLimpieza)->not->toBeNull()
        ->and($solicitudLimpieza->estado)->toBe(EstadoLimpieza::Pendiente);
});

test('rechaza abrir un pedido si la mesa no esta disponible', function (): void {
    $mesaOcupada = Espacio::query()->create([
        'codigo' => 'MESA-09',
        'nombre' => 'Mesa 09',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Ocupado,
        'activo' => true,
    ]);

    $interactor = new AbrirPedidoMesa;
    $interactor->ejecutar($mesaOcupada);
})->throws(DomainException::class, 'no está disponible');
