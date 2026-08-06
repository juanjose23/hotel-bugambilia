<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\CargoFacturacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaCargo;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;
use RuntimeException;

final class CuentaRepositorio implements CuentaRepositorioInterface
{
    public function abrir(Cuenta $cuenta, array $datos): Cuenta
    {
        $cuenta->update($datos);

        return $cuenta->refresh();
    }

    public function crear(array $datos): Cuenta
    {
        return Cuenta::query()->create($datos);
    }

    public function actualizar(Cuenta $cuenta, array $datos): Cuenta
    {
        $cuenta->update($datos);

        return $cuenta->refresh();
    }

    public function refrescar(Cuenta $cuenta): Cuenta
    {
        return $cuenta->refresh();
    }

    public function bloquear(int $cuentaId): Cuenta
    {
        return Cuenta::query()
            ->where('id', $cuentaId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function subtotalDetallesActivos(Cuenta $cuenta): float
    {
        return (float) $cuenta->detalles()
            ->where('estado', EstadoGeneral::Activo->value)
            ->sum('subtotal');
    }

    public function sumaCargosActivos(Cuenta $cuenta, TipoCargo $tipo): float
    {
        return (float) $cuenta->cargos()
            ->where('tipo', $tipo->value)
            ->where('estado', EstadoGeneral::Activo->value)
            ->sum('monto');
    }

    public function sumaPagosAplicados(Cuenta $cuenta): float
    {
        return (float) $cuenta->pagos()
            ->where('estado', EstadoPago::APLICADO->value)
            ->sum('monto');
    }

    public function cargosFacturacionObligatorios(): Collection
    {
        return CargoFacturacion::query()
            ->where('estado', EstadoGeneral::Activo->value)
            ->where('obligatorio', true)
            ->get();
    }

    public function cuentaCargoVigente(Cuenta $cuenta, int $cargoId): ?CuentaCargo
    {
        /** @var CuentaCargo|null $cuentaCargo */
        $cuentaCargo = $cuenta->cargos()
            ->where('cargo_id', $cargoId)
            ->where('estado', EstadoGeneral::Activo->value)
            ->first();

        return $cuentaCargo;
    }

    public function descuentoManualDeCuenta(Cuenta $cuenta): ?CuentaCargo
    {
        /** @var CuentaCargo|null $cuentaCargo */
        $cuentaCargo = $cuenta->cargos()
            ->where('tipo', TipoCargo::Descuento->value)
            ->where('modo_calculo', ModoCargo::Manual->value)
            ->where('estado', EstadoGeneral::Activo->value)
            ->latest('id')
            ->first();

        return $cuentaCargo;
    }

    public function cuentaCargoPorCodigo(Cuenta $cuenta, string $codigo): ?CuentaCargo
    {
        /** @var CuentaCargo|null $cuentaCargo */
        $cuentaCargo = $cuenta->cargos()
            ->where('codigo', $codigo)
            ->where('estado', EstadoGeneral::Activo->value)
            ->latest('id')
            ->first();

        return $cuentaCargo;
    }

    public function actualizarCuentaCargo(CuentaCargo $cuentaCargo, array $datos): void
    {
        $cuentaCargo->update($datos);
    }

    public function crearCuentaCargo(Cuenta $cuenta, array $datos): CuentaCargo
    {
        $datos['moneda_id'] = $this->resolverMoneda($cuenta, $datos);

        /** @var CuentaCargo $cuentaCargo */
        $cuentaCargo = $cuenta->cargos()->create($datos);

        return $cuentaCargo;
    }

    public function crearDetalle(Cuenta $cuenta, array $datos): CuentaDetalle
    {
        $datos['moneda_id'] = $this->resolverMoneda($cuenta, $datos);

        /** @var CuentaDetalle $detalle */
        $detalle = $cuenta->detalles()->create($datos);

        return $detalle;
    }

    public function detalleActivoConOrigen(Cuenta $cuenta, string $origenType, int $origenId): ?CuentaDetalle
    {
        /** @var CuentaDetalle|null $detalle */
        $detalle = $cuenta->detalles()
            ->where('origen_type', $origenType)
            ->where('origen_id', $origenId)
            ->where('estado', EstadoGeneral::Activo->value)
            ->latest('id')
            ->first();

        return $detalle;
    }

    public function actualizarDetalle(CuentaDetalle $detalle, array $datos): CuentaDetalle
    {
        $detalle->update($datos);

        return $detalle->refresh();
    }

    public function crearPago(Cuenta $cuenta, array $datos): PagoCuenta
    {
        /** @var PagoCuenta $pago */
        $pago = $cuenta->pagos()->create($datos);

        return $pago;
    }

    /**
     * Asegura que cada cargo/detalle quede asociado a una moneda.
     * Si la cuenta no la define, se usa la moneda predeterminada del sistema.
     *
     * @param  array<string, mixed>  $datos
     */
    private function resolverMoneda(Cuenta $cuenta, array $datos): int
    {
        $monedaId = $datos['moneda_id'] ?? $cuenta->moneda_id;

        if (is_numeric($monedaId)) {
            return (int) $monedaId;
        }

        $predeterminada = Moneda::query()->where('es_predeterminada', true)->value('id');

        if (is_numeric($predeterminada)) {
            return (int) $predeterminada;
        }

        $cualquiera = Moneda::query()->value('id');

        if (is_numeric($cualquiera)) {
            return (int) $cualquiera;
        }

        throw new RuntimeException('No existe ninguna moneda registrada en el sistema.');
    }

    public function existeDetalleConOrigen(Cuenta $cuenta, string $origenType, int $origenId): bool
    {
        return CuentaDetalle::query()
            ->where('cuenta_id', $cuenta->id)
            ->where('origen_type', $origenType)
            ->where('origen_id', $origenId)
            ->where('estado', EstadoGeneral::Activo->value)
            ->exists();
    }

    public function detallesActivos(Cuenta $cuenta): Collection
    {
        return $cuenta->detalles()
            ->where('estado', EstadoGeneral::Activo->value)
            ->get();
    }

    public function crearVenta(array $datos): Venta
    {
        return Venta::query()->create($datos);
    }

    public function crearVentaDetalle(Venta $venta, array $datos): VentaDetalle
    {
        /** @var VentaDetalle $detalle */
        $detalle = $venta->detalles()->create($datos);

        return $detalle;
    }

    public function cargarPedidoEnCuenta(Pedido $pedido, int $cuentaId): void
    {
        $pedido->update([
            'cuenta_id' => $cuentaId,
            'estado' => EstadoPedido::CARGADO_A_HABITACION,
            'cargado_en' => now(),
        ]);
    }

    public function pedidoConItemsCargados(Pedido $pedido): Pedido
    {
        return $pedido->loadMissing('items.plato');
    }

    public function monedaPredeterminada(): ?Moneda
    {
        /** @var Moneda|null $moneda */
        $moneda = Moneda::query()
            ->where('es_predeterminada', true)
            ->first();

        return $moneda;
    }

    public function cargosFacturacionActivos(): Collection
    {
        return CargoFacturacion::query()
            ->activos()
            ->orderBy('orden')
            ->get();
    }

    public function totalCargosObligatorios(Cuenta $cuenta): float
    {
        return (float) $cuenta->cargos()
            ->whereHas('cargoCatalogo', fn ($q) => $q->where('obligatorio', true))
            ->sum('monto');
    }

    public function pedidoClienteDeCuenta(int $cuentaId): ?Pedido
    {
        /** @var Pedido|null $pedido */
        $pedido = Pedido::query()
            ->where('cuenta_id', $cuentaId)
            ->whereNotNull('cliente_id')
            ->first();

        return $pedido;
    }

    /** @return Collection<int, Cuenta> */
    public function cuentasAbiertasDeReserva(int $reservaId): Collection
    {
        return Cuenta::query()
            ->where('reserva_id', $reservaId)
            ->where('estado', EstadoCuenta::ABIERTA)
            ->get();
    }
}
