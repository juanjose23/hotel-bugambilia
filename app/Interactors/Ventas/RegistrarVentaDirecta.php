<?php

declare(strict_types=1);

namespace App\Interactors\Ventas;

use App\Actions\Ventas\GenerarNumeroVentaDirecta;
use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Facturacion\TipoFactura;
use App\Interactors\Facturacion\EmitirFacturaDesdeVenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Persistencia\Ventas\VentaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarVentaDirecta
{
    public function __construct(
        private VentaRepositorioInterface $ventas,
        private GenerarNumeroVentaDirecta $generarNumeroVenta,
        private EmitirFacturaDesdeVenta $emitirFacturaDesdeVenta,
    ) {}

    /**
     * @param  array<int, array{concepto: string, cantidad: float|int, precio_unitario: float|int, descuento?: float|int|null}>  $items
     * @param  array<string, mixed>  $datosFiscales
     */
    public function ejecutar(
        ?int $clienteId,
        int $monedaId,
        array $items,
        ?int $usuarioId = null,
        bool $emitirFactura = false,
        ?int $facturaSerieId = null,
        ?TipoFactura $tipoFactura = null,
        array $datosFiscales = [],
    ): Venta {
        if (empty($items)) {
            throw new DomainException('No se puede registrar una venta sin ítems.');
        }

        return DB::transaction(function () use (
            $clienteId,
            $monedaId,
            $items,
            $usuarioId,
            $emitirFactura,
            $facturaSerieId,
            $tipoFactura,
            $datosFiscales
        ): Venta {
            $subtotalVenta = 0.0;
            $descuentoTotalVenta = 0.0;
            $ivaTotalVenta = 0.0;
            $lineasProcesadas = [];

            foreach ($items as $item) {
                $cantidad = max(0.01, (float) $item['cantidad']);
                $precioUnitario = max(0.0, (float) $item['precio_unitario']);
                $descuentoLinea = max(0.0, (float) ($item['descuento'] ?? 0.0));
                $subtotalLineaBruto = round($cantidad * $precioUnitario, 2);
                $subtotalLinea = max(0.0, round($subtotalLineaBruto - $descuentoLinea, 2));
                $ivaLinea = round($subtotalLinea * 0.15, 2);
                $totalLinea = round($subtotalLinea + $ivaLinea, 2);

                $subtotalVenta += $subtotalLinea;
                $descuentoTotalVenta += $descuentoLinea;
                $ivaTotalVenta += $ivaLinea;

                $lineasProcesadas[] = [
                    'concepto' => (string) $item['concepto'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalLinea,
                    'descuento' => $descuentoLinea,
                    'impuesto' => $ivaLinea,
                    'total_linea' => $totalLinea,
                ];
            }

            $totalVenta = round($subtotalVenta + $ivaTotalVenta, 2);
            $numeroVenta = $this->generarNumeroVenta->ejecutar();

            $venta = $this->ventas->crear([
                'numero_venta' => $numeroVenta,
                'cuenta_id' => null,
                'cliente_id' => $clienteId,
                'moneda_id' => $monedaId,
                'subtotal' => $subtotalVenta,
                'descuento_total' => $descuentoTotalVenta,
                'impuesto_total' => $ivaTotalVenta,
                'servicio_total' => 0.00,
                'propina_total' => 0.00,
                'recargo_total' => 0.00,
                'total' => $totalVenta,
                'estado' => EstadoVenta::Emitida,
                'datos_fiscales' => array_merge([
                    'canal' => 'pos_mostrador',
                    'creado_en' => now()->toIso8601String(),
                ], $datosFiscales),
                'creada_por' => $usuarioId,
            ]);

            foreach ($lineasProcesadas as $linea) {
                $this->ventas->crearDetalle($venta, $linea);
            }

            if ($emitirFactura) {
                $this->emitirFacturaDesdeVenta->ejecutar(
                    venta: $venta,
                    serieId: $facturaSerieId,
                    tipo: $tipoFactura ?? TipoFactura::Contado,
                    datosReceptor: $datosFiscales,
                    usuarioId: $usuarioId,
                );
            }

            return $venta->refresh()->load(['detalles', 'facturas.serie', 'cliente.persona', 'moneda']);
        });
    }
}
