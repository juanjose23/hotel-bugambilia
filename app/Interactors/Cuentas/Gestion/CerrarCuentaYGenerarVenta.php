<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\Actions\Ventas\GenerarNumeroVenta;
use App\BusinessLogic\Ventas\ConstruirFotografiaVenta;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Facturacion\TipoFactura;
use App\Interactors\Facturacion\EmitirFacturaDesdeVenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Ventas\VentaRepositorioInterface;
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
        private readonly VentaRepositorioInterface $ventas,
        private readonly GenerarNumeroVenta $generarNumeroVenta,
        private readonly ConstruirFotografiaVenta $fotografiaVenta,
        private readonly EmitirFacturaDesdeVenta $emitirFacturaDesdeVenta,
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
            $numeroVenta = $this->generarNumeroVenta->ejecutar($cuenta->id);

            // Crear la venta con fotografía histórica
            $venta = $this->ventas->crear(
                $this->fotografiaVenta->cabecera($cuenta, $numeroVenta, $usuarioId, $datosFiscales),
            );

            // Generar los detalles de la venta (fotografía de cada consumo)
            foreach ($this->cuentas->detallesActivos($cuenta) as $detalle) {
                $this->ventas->crearDetalle($venta, $this->fotografiaVenta->detalle($detalle));
            }

            // Cerrar la cuenta
            $this->cuentas->actualizar($cuenta, [
                'estado' => EstadoCuenta::CERRADA,
                'cerrada_at' => now(),
                'cerrada_por' => $usuarioId,
                'actualizado_por' => $usuarioId,
            ]);

            if (($datosFiscales['emitir_factura'] ?? false) === true) {
                $tipoFacturaValor = $datosFiscales['tipo_factura'] ?? null;
                $tipoFactura = is_numeric($tipoFacturaValor)
                    ? TipoFactura::tryFrom((int) $tipoFacturaValor)
                    : null;

                $this->emitirFacturaDesdeVenta->ejecutar(
                    venta: $venta,
                    serieId: is_numeric($datosFiscales['factura_serie_id'] ?? null) ? (int) $datosFiscales['factura_serie_id'] : null,
                    tipo: $tipoFactura ?? TipoFactura::Contado,
                    datosReceptor: is_array($datosFiscales['receptor'] ?? null) ? $datosFiscales['receptor'] : $datosFiscales,
                    usuarioId: $usuarioId,
                );
            }

            return $venta->refresh()->load('facturas.detalles');
        });
    }
}
