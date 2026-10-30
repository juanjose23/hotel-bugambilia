<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\CalcularArqueo;
use App\Enums\Cuentas\MetodoPago;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\CerrarCuentaYGenerarVenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final readonly class CobrarDesdeCaja
{
    public function __construct(private CajaRepositorio $cajas, private CuentaRepositorioInterface $cuentas, private RegistrarPagoCuenta $pago, private CerrarCuentaYGenerarVenta $cierre, private CalcularArqueo $calculo) {}

    /** @return array{pago_id:int,venta:Venta|null} */
    public function ejecutar(int $actorId, int $cuentaId, float $recibido, bool $factura, ?int $serieId = null, ?string $clave = null): array
    {
        return DB::transaction(function () use ($actorId, $cuentaId, $recibido, $factura, $serieId, $clave): array {
            if (! config('tesoreria.habilitado') || $clave === null || ! Str::isUuid($clave)) {
                throw new DomainException('El cobro requiere caja habilitada y una clave de operación válida.');
            }
            $centavos = $this->calculo->centavos($recibido);
            $huella = hash('sha256', json_encode([$actorId, $cuentaId, $centavos, $factura, $serieId], JSON_THROW_ON_ERROR));
            $actor = $this->cajas->usuario($actorId);
            Gate::forUser($actor)->authorize('Operar:Caja');
            if ($factura) {
                Gate::forUser($actor)->authorize('Create:Factura');
            }
            $cuenta = $this->cuentas->bloquear($cuentaId);
            $previa = $this->cajas->operacionCobro($clave);
            if ($previa !== null) {
                if ($previa->huella_peticion !== $huella) {
                    throw new DomainException('La clave del cobro ya corresponde a otros datos.');
                }

                return ['pago_id' => $previa->pago_cuenta_id, 'venta' => $previa->venta];
            }
            if ((float) $cuenta->saldo <= 0) {
                throw new DomainException('La cuenta ya no tiene saldo pendiente.');
            }
            $pago = $this->pago->ejecutar($cuenta, MetodoPago::EFECTIVO, $recibido, monedaId: $cuenta->moneda_id, usuarioId: $actorId);
            $cuenta = $this->cuentas->refrescar($cuenta);
            $venta = null;
            if ((float) $cuenta->saldo <= 0) {
                $venta = $this->cierre->ejecutar($cuenta, $actorId, ['emitir_factura' => $factura, 'factura_serie_id' => $serieId]);
            }

            $this->cajas->guardarOperacionCobro(['clave' => $clave, 'cuenta_id' => $cuentaId, 'usuario_id' => $actorId, 'pago_cuenta_id' => $pago->id, 'venta_id' => $venta?->id, 'huella_peticion' => $huella]);

            return ['pago_id' => $pago->id, 'venta' => $venta];
        });
    }
}
