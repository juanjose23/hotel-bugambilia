<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\BusinessLogic\Cuentas\ValidarCuenta;
use App\Enums\Shared\EstadoGeneral;
use App\Events\Cuentas\DetalleCuentaRegistrado;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Registra un consumo puro en el detalle de la cuenta.
 * Los impuestos, descuentos y cargos se aplican por separado en cuenta_cargos.
 *
 * Uso desde módulos:
 *   - Restaurante: origen = PedidoItem
 *   - Estancias: origen = Estancia
 *   - Spa/Lavandería: origen = Servicio
 */
final class RegistrarDetalleCuenta
{
    public function __construct(
        private readonly ValidarCuenta $validarCuenta,
        private readonly RecalcularCuenta $recalcularCuenta,
        private readonly CuentaRepositorioInterface $cuentas,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadatos
     */
    public function ejecutar(
        Cuenta $cuenta,
        string $concepto,
        float $precioUnitario,
        float $cantidad = 1.0,
        ?Model $origen = null,
        ?int $espacioId = null,
        ?int $creadorId = null,
        ?string $descripcion = null,
        ?int $tipoDetalle = null,
        ?array $metadatos = null,
    ): CuentaDetalle {
        $this->validarCuenta->puedeRegistrarCargo($cuenta);

        $subtotal = round($precioUnitario * max(1, $cantidad), 2);

        $this->validarCuenta->validarLimiteAutorizado($cuenta, $subtotal);

        return DB::transaction(function () use (
            $cuenta, $concepto, $precioUnitario,
            $cantidad, $subtotal, $origen, $espacioId, $creadorId,
            $descripcion, $tipoDetalle, $metadatos
        ): CuentaDetalle {
            $detalle = $this->cuentas->crearDetalle($cuenta, [
                'moneda_id' => $cuenta->moneda_id,
                'origen_type' => $origen?->getMorphClass(),
                'origen_id' => $origen?->getKey(),
                'tipo_detalle' => $tipoDetalle,
                'espacio_id' => $espacioId,
                'concepto' => $concepto,
                'descripcion' => $descripcion,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'estado' => EstadoGeneral::Activo->value,
                'metadatos' => $metadatos,
                'creador_id' => $creadorId,
            ]);

            $this->recalcularCuenta->ejecutar($cuenta, $creadorId);

            DetalleCuentaRegistrado::dispatch($detalle);

            return $detalle;
        });
    }
}
