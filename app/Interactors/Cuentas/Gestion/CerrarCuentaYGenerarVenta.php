<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoVenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Cierra una cuenta y genera el documento de venta definitivo
 * con fotografía histórica de todos los valores.
 */
final class CerrarCuentaYGenerarVenta
{
    public function __construct(
        private readonly RecalcularCuenta $recalcularCuenta,
        private readonly CuentaRepositorioInterface $cuentas,
    ) {}

    /**
     * @param  array<string, mixed>|null  $datosFiscales
     */
    public function ejecutar(Cuenta $cuenta, ?int $usuarioId = null, ?array $datosFiscales = null): Venta
    {
        if (! $cuenta->estado->puedeCerrarse()) {
            throw new DomainException(
                "La cuenta {$cuenta->numero_cuenta} no puede cerrarse en estado '{$cuenta->estado->getLabel()}'.",
            );
        }

        return DB::transaction(function () use ($cuenta, $usuarioId, $datosFiscales): Venta {
            // Bloquear cuenta para evitar concurrencia
            $cuenta = $this->cuentas->bloquear($cuenta->id);

            // Recalcular por última vez antes de congelar
            $cuenta = $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);

            if ($cuenta->tieneSaldoPendiente()) {
                throw new DomainException(
                    'No se puede cerrar la cuenta con saldo pendiente de C$ '.number_format((float) $cuenta->saldo, 2).'.',
                );
            }

            // Generar número de venta
            $numeroVenta = sprintf('VTA-%s-%06d', now()->format('Ymd'), $cuenta->id);

            // Crear la venta con fotografía histórica
            $venta = $this->cuentas->crearVenta([
                'numero_venta' => $numeroVenta,
                'cuenta_id' => $cuenta->id,
                'cliente_id' => $cuenta->cliente_id,
                'moneda_id' => $cuenta->moneda_id,
                'subtotal' => $cuenta->subtotal,
                'descuento_total' => $cuenta->descuento_total,
                'impuesto_total' => $cuenta->impuesto_total,
                'servicio_total' => $cuenta->cargo_servicio_total,
                'propina_total' => $cuenta->propina_total,
                'recargo_total' => $cuenta->recargo_total,
                'total' => $cuenta->total,
                'estado' => EstadoVenta::Emitida,
                'datos_fiscales' => $datosFiscales,
                'creada_por' => $usuarioId,
            ]);

            // Generar los detalles de la venta (fotografía de cada consumo)
            foreach ($this->cuentas->detallesActivos($cuenta) as $detalle) {
                $this->cuentas->crearVentaDetalle($venta, [
                    'concepto' => $detalle->concepto,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                    'descuento' => 0,
                    'impuesto' => 0,
                    'servicio' => 0,
                    'propina' => 0,
                    'recargo' => 0,
                    'total_linea' => $detalle->subtotal,
                    'origen_type' => $detalle->origen_type,
                    'origen_id' => $detalle->origen_id,
                ]);
            }

            // Cerrar la cuenta
            $this->cuentas->actualizar($cuenta, [
                'estado' => EstadoCuenta::CERRADA,
                'cerrada_at' => now(),
                'cerrada_por' => $usuarioId,
                'actualizado_por' => $usuarioId,
            ]);

            return $venta;
        });
    }
}
