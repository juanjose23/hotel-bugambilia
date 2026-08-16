<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\BusinessLogic\Cuentas\ValidarCuenta;
use App\Enums\Shared\EstadoGeneral;
use App\Events\Cuentas\DetalleCuentaRegistrado;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\User;
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
        int|string|null $espacioId = null,
        int|string|null $creadorId = null,
        ?string $descripcion = null,
        ?int $tipoDetalle = null,
        ?array $metadatos = null,
    ): CuentaDetalle {
        $this->validarCuenta->puedeRegistrarCargo($cuenta);

        $subtotal = round($precioUnitario * max(1, $cantidad), 2);

        $this->validarCuenta->validarLimiteAutorizado($cuenta, $subtotal);

        $espacioIdResuelto = $this->enteroOpcional($espacioId);
        $creadorIdResuelto = $this->enteroOpcional($creadorId);

        return DB::transaction(function () use (
            $cuenta, $concepto, $precioUnitario,
            $cantidad, $subtotal, $origen, $espacioIdResuelto, $creadorIdResuelto,
            $descripcion, $tipoDetalle, $metadatos
        ): CuentaDetalle {
            $creadorIdFinal = ($creadorIdResuelto !== null && User::query()->where('id', $creadorIdResuelto)->exists())
                ? $creadorIdResuelto
                : null;

            $detalle = $this->cuentas->crearDetalle($cuenta, [
                'moneda_id' => $cuenta->moneda_id,
                'origen_type' => $origen?->getMorphClass(),
                'origen_id' => $origen?->getKey(),
                'tipo_detalle' => $tipoDetalle,
                'espacio_id' => $espacioIdResuelto,
                'concepto' => $concepto,
                'descripcion' => $descripcion,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'estado' => EstadoGeneral::Activo->value,
                'metadatos' => $metadatos,
                'creador_id' => $creadorIdFinal,
            ]);

            $this->recalcularCuenta->ejecutar($cuenta, $creadorIdFinal);

            DetalleCuentaRegistrado::dispatch($detalle);

            return $detalle;
        });
    }

    private function enteroOpcional(mixed $valor): ?int
    {
        if (is_int($valor)) {
            return $valor > 0 ? $valor : null;
        }

        if (is_string($valor) && ctype_digit($valor)) {
            $entero = (int) $valor;

            return $entero > 0 ? $entero : null;
        }

        return null;
    }
}
