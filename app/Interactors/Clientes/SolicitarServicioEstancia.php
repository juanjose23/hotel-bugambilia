<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Servicios\ServicioRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class SolicitarServicioEstancia
{
    public function __construct(
        private CuentaRepositorioInterface $cuentaRepositorio,
        private ReservaRepositorioInterface $reservaRepositorio,
        private ServicioRepositorioInterface $servicioRepositorio,
    ) {}

    /**
     * @param  array{servicio_id: int, cantidad: float, notas?: string|null}  $datos
     * @return array<string, mixed>
     */
    public function ejecutar(int $reservaId, array $datos, ?User $user = null): array
    {
        return DB::transaction(function () use ($reservaId, $datos, $user): array {
            $reserva = $this->reservaRepositorio->obtenerPorIdConCuentasYMonedaConLock($reservaId);
            if ($reserva === null) {
                throw new DomainException("La reserva #{$reservaId} no existe.");
            }

            if (! in_array($reserva->estado, [EstadoReserva::CONFIRMADA, EstadoReserva::CHECKED_IN], true)) {
                throw new DomainException('Solo es posible solicitar servicios para reservaciones confirmadas o con estancia activa.');
            }

            $servicio = $this->servicioRepositorio->buscarActivoConPrecios((int) $datos['servicio_id']);
            if ($servicio === null) {
                throw new DomainException('El servicio solicitado no está disponible en este momento.');
            }

            $cantidad = max(1.0, (float) $datos['cantidad']);
            $precioObj = $servicio->precios->first();
            $precioUnitario = $precioObj !== null ? (float) $precioObj->precio : 0.0;
            $subtotal = round($cantidad * $precioUnitario, 2);
            $total = $subtotal;

            /** @var Cuenta|null $cuenta */
            $cuenta = $reserva->cuentas->firstWhere('estado', EstadoCuenta::ABIERTA);

            if ($cuenta === null) {
                $monedaPredeterminada = $this->cuentaRepositorio->monedaPredeterminada();
                $monedaId = $reserva->moneda_id ?? ($monedaPredeterminada !== null ? $monedaPredeterminada->id : 1);
                $cuenta = $this->cuentaRepositorio->crear([
                    'numero_cuenta' => 'CTA-RES-'.$reserva->id.'-'.time(),
                    'tipo_cuenta' => TipoCuenta::ESTANCIA,
                    'estado' => EstadoCuenta::ABIERTA,
                    'cliente_id' => $reserva->cliente_id,
                    'reserva_id' => $reserva->id,
                    'moneda_id' => $monedaId,
                    'subtotal' => 0.00,
                    'total' => 0.00,
                    'total_pagado' => 0.00,
                    'saldo' => 0.00,
                    'abierta_at' => now(),
                    'abierta_por' => $user?->id,
                ]);
            }

            $notas = isset($datos['notas']) ? trim($datos['notas']) : null;

            $this->cuentaRepositorio->crearDetalle($cuenta, [
                'moneda_id' => $cuenta->moneda_id,
                'origen_type' => Servicio::class,
                'origen_id' => $servicio->id,
                'concepto' => "Servicio: {$servicio->nombre}",
                'descripcion' => $notas,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
                'total' => $total,
                'estado' => 1,
                'creador_id' => $user?->id,
                'metadatos' => [
                    'solicitado_desde_portal' => true,
                    'fecha_solicitud' => now()->toIso8601String(),
                    'notas' => $notas,
                ],
            ]);

            // Recalcular balance de la cuenta
            $nuevoSubtotal = (float) $cuenta->detalles()->sum('subtotal');
            $nuevoTotal = round($nuevoSubtotal + (float) $cuenta->impuesto_total - (float) $cuenta->descuento_total, 2);
            $nuevoSaldo = round(max(0.0, $nuevoTotal - (float) $cuenta->total_pagado), 2);

            $this->cuentaRepositorio->actualizar($cuenta, [
                'subtotal' => $nuevoSubtotal,
                'total' => $nuevoTotal,
                'saldo' => $nuevoSaldo,
            ]);

            $reserva->actualizarOCrearEntradaBitacora('solicitud_servicio', [
                'servicio_id' => $servicio->id,
                'servicio_nombre' => $servicio->nombre,
                'cantidad' => $cantidad,
                'total' => $total,
                'solicitado_at' => now()->toIso8601String(),
                'usuario_id' => $user?->id,
            ]);

            return [
                'reserva_id' => $reserva->id,
                'cuenta_id' => $cuenta->id,
                'servicio' => $servicio->nombre,
                'total_agregado' => $total,
                'nuevo_saldo_cuenta' => $nuevoSaldo,
            ];
        });
    }
}
