<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\BusinessLogic\Restaurante\Cobro\CalcularVueltoCobro;
use App\BusinessLogic\Restaurante\Cobro\ResolverMonedaCobro;
use App\BusinessLogic\Restaurante\Cobro\ValidarMetodoPago;
use App\BusinessLogic\Restaurante\Cuentas\CalcularTotalesCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\AbrirCuenta;
use App\Interactors\Cuentas\Gestion\CerrarCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Interactors\Limpieza\Ejecucion\RegistrarSolicitudLimpieza;
use App\Interactors\Restaurante\Mesas\CambiarEstadoMesa;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CerrarPedidoMesa
{
    public function __construct(
        private readonly CalcularTotalesCuenta $calcularTotales,
        private readonly RegistrarDetalleCuenta $registrarDetalle,
        private readonly AbrirCuenta $abrirCuenta,
        private readonly RegistrarPagoCuenta $registrarPago,
        private readonly CerrarCuenta $cerrarCuenta,
        private readonly CambiarEstadoMesa $cambiarEstadoMesa,
        private readonly RegistrarSolicitudLimpieza $registrarLimpieza,
        private readonly ValidarMetodoPago $validarMetodoPago,
        private readonly ResolverMonedaCobro $resolverMonedaCobro,
        private readonly CalcularVueltoCobro $calcularVuelto,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * Cierra el pedido de la mesa, procesa el cobro o cargo a cuenta y pasa la mesa a estado Sucio/Limpieza.
     */
    public function ejecutar(
        Pedido $pedido,
        bool $cargarAHabitacion = false,
        ?Cuenta $cuentaEstancia = null,
        ?int $usuarioId = null,
        ?MetodoPago $metodoPago = null,
        ?float $montoRecibido = null,
        ?string $referenciaPago = null,
        ?int $clienteId = null,
        ?int $monedaId = null,
    ): Pedido {
        if (in_array($pedido->estado, [EstadoPedido::PAGADO, EstadoPedido::CARGADO_A_HABITACION], true)) {
            throw new DomainException("El pedido #{$pedido->codigo} ya fue cerrado previamente.");
        }

        return DB::transaction(function () use (
            $pedido,
            $cargarAHabitacion,
            $cuentaEstancia,
            $usuarioId,
            $metodoPago,
            $montoRecibido,
            $referenciaPago,
            $clienteId,
            $monedaId,
        ): Pedido {
            $pedido->loadMissing('items.plato', 'mesa');

            $totales = $this->calcularTotales->calcular($pedido);
            $subtotalPedido = $totales['subtotal'];

            // Convertir montoRecibido de moneda extranjera a NIO antes de validar
            if ($montoRecibido !== null) {
                $monedaPago = $this->resolverMonedaCobro->resolverMoneda($monedaId);
                $monedaBase = $this->resolverMonedaCobro->resolverMoneda(null);

                if ($monedaPago->codigo !== $monedaBase->codigo) {
                    $tasa = $this->resolverMonedaCobro->resolverTasaCambio($monedaPago->codigo, $monedaBase->codigo);
                    $montoRecibido = round($montoRecibido * $tasa, 2);
                }
            }

            if (! $cargarAHabitacion && $metodoPago !== null) {
                $this->validarMetodoPago->validar($metodoPago, $montoRecibido ?? 0.0);
            }

            if (! $cargarAHabitacion && $montoRecibido !== null && $montoRecibido < $subtotalPedido) {
                throw new DomainException("El monto recibido (C$ {$montoRecibido}) es menor al subtotal (C$ {$subtotalPedido}).");
            }

            $pedido->subtotal = $subtotalPedido;

            if ($cargarAHabitacion) {
                $this->procesarCargoAHabitacion($pedido, $subtotalPedido, $cuentaEstancia, $usuarioId);
            } else {
                $this->procesarPagoDirecto(
                    $pedido,
                    $subtotalPedido,
                    $usuarioId,
                    $metodoPago,
                    $montoRecibido,
                    $referenciaPago,
                    $clienteId,
                    $monedaId,
                );
            }

            $cuenta = $pedido->cuenta;
            if ($cuenta instanceof Cuenta) {
                $pedido->total = (float) $cuenta->refresh()->total;
            }

            $pedido->cerrado_en = now();
            $this->repositorio->guardarPedido($pedido);

            $this->gestionarMesaYSolicitarLimpieza($pedido, $usuarioId);

            return $pedido;
        });
    }

    private function procesarCargoAHabitacion(
        Pedido $pedido,
        float $subtotal,
        ?Cuenta $cuentaEstancia,
        ?int $usuarioId,
    ): void {
        $cuenta = $cuentaEstancia ?? ($pedido->cuenta ?? null);

        if (! $cuenta instanceof Cuenta) {
            throw new DomainException('Debe seleccionar una cuenta activa para cargar el pedido.');
        }

        $this->registrarDetalle->ejecutar(
            cuenta: $cuenta,
            concepto: "Consumo Restaurante (Comanda #{$pedido->codigo})",
            precioUnitario: $subtotal,
            cantidad: 1,
            origen: $pedido,
            creadorId: $usuarioId,
        );

        $pedido->estado = EstadoPedido::CARGADO_A_HABITACION;
        $pedido->cuenta_id = $cuenta->id;
        $pedido->cargado_en = now();
    }

    private function procesarPagoDirecto(
        Pedido $pedido,
        float $subtotal,
        ?int $usuarioId,
        ?MetodoPago $metodoPago,
        ?float $montoRecibido,
        ?string $referenciaPago,
        ?int $clienteId,
        ?int $monedaId,
    ): void {
        $monedaResueltaId = $this->resolverMonedaCobro->resolverMoneda($monedaId)->getKey();
        $monedaResuelta = is_numeric($monedaResueltaId) ? (int) $monedaResueltaId : null;

        $cuenta = $pedido->cuenta;
        if (! $cuenta instanceof Cuenta) {
            $cuenta = $this->abrirCuenta->ejecutar(
                tipo: TipoCuenta::RESTAURANTE_DIRECTO,
                cliente: $clienteId !== null ? $this->repositorio->obtenerClientePorId($clienteId) : null,
                monedaId: $monedaResuelta,
                usuarioId: $usuarioId,
            );
            $pedido->cuenta_id = $cuenta->id;
        }

        $pedido->estado = EstadoPedido::PAGADO;
        $pedido->cuenta_id = $cuenta->id;

        if ($metodoPago !== null && $montoRecibido !== null) {
            $propina = $this->calcularVuelto->calcularPropinaImplicita($montoRecibido, $subtotal);

            $this->registrarPago->ejecutar(
                cuenta: $cuenta,
                metodoPago: $metodoPago,
                monto: $montoRecibido,
                propina: $propina,
                estado: EstadoPago::APLICADO,
                referenciaTransaccion: $referenciaPago,
                monedaId: $monedaResuelta,
                usuarioId: $usuarioId,
            );

            $cuenta->refresh();
            if ((float) $cuenta->saldo <= 0.0) {
                $this->cerrarCuenta->ejecutar($cuenta, $usuarioId);
            }
        }
    }

    private function gestionarMesaYSolicitarLimpieza(Pedido $pedido, ?int $usuarioId): void
    {
        $mesa = $pedido->mesa;
        if (! $mesa instanceof Espacio) {
            return;
        }

        $pedidosActivos = $this->repositorio->existeOtroPedidoActivoEnMesa($mesa->id, $pedido->id);

        if (! $pedidosActivos && $mesa->estado === EstadoEspacio::Ocupado) {
            $this->cambiarEstadoMesa->ejecutar($mesa->id, EstadoEspacio::Sucio, MotivoTransicionMesa::CierrePedido);

            $this->registrarLimpieza->execute(
                limpiable: $mesa,
                prioridad: 'alta',
                notas: "Limpieza requerida tras cierre de comanda #{$pedido->codigo}",
            );
        }
    }
}
