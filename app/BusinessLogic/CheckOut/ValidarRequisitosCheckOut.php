<?php

declare(strict_types=1);

namespace App\BusinessLogic\CheckOut;

use App\Enums\Estancias\EstadoEstancia;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Queries\Cuentas\ObtenerSaldoPendienteReservaQuery;
use DomainException;

final class ValidarRequisitosCheckOut
{
    public function __construct(
        private readonly ObtenerSaldoPendienteReservaQuery $saldoPendiente = new ObtenerSaldoPendienteReservaQuery,
    ) {}

    /**
     * @param  array<string, mixed>  $datosCheckOut
     */
    public function validar(Estancia $estancia, array $datosCheckOut = []): void
    {
        $this->validarEstadoEstancia($estancia);
        $this->validarSaldoCuenta($estancia, (bool) ($datosCheckOut['credito_autorizado'] ?? false));
        $this->validarDevolucionLlaves($estancia, $datosCheckOut);
    }

    private function validarEstadoEstancia(Estancia $estancia): void
    {
        if (! in_array($estancia->estado, [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA], true)) {
            throw new DomainException("Solo se puede realizar Check-out en estancias activas o extendidas. Estado actual: {$estancia->estado->getLabel()}.");
        }
    }

    private function validarSaldoCuenta(Estancia $estancia, bool $creditoAutorizado): void
    {
        $saldoPendiente = $this->saldoPendiente->ejecutar((int) $estancia->reserva_id);

        if ($saldoPendiente > 0 && ! $creditoAutorizado) {
            throw new DomainException(
                'No se puede realizar el check-out mientras existan cuentas con saldo pendiente de C$ '.
                number_format($saldoPendiente, 2, '.', ',').
                '. Requiere liquidación o autorización de crédito corporativo.',
            );
        }
    }

    /**
     * @param  array<string, mixed>  $datosCheckOut
     */
    private function validarDevolucionLlaves(Estancia $estancia, array $datosCheckOut): void
    {
        $llavesEntregadas = (int) ($estancia->cantidad_llaves ?? 1);
        $llavesDevueltas = is_numeric($datosCheckOut['llaves_devueltas'] ?? null)
            ? (int) $datosCheckOut['llaves_devueltas']
            : $llavesEntregadas;
        $excepcionLlaves = (bool) ($datosCheckOut['autorizar_llaves_pendientes'] ?? false);

        if ($llavesDevueltas < $llavesEntregadas && ! $excepcionLlaves) {
            throw new DomainException(
                "Faltan llaves por devolver ({$llavesDevueltas} de {$llavesEntregadas}). Requiere autorización especial para omitir.",
            );
        }
    }
}
