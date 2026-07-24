<?php

declare(strict_types=1);

namespace App\BusinessLogic\Huespedes;

use App\Enums\Reservas\TipoHuesped;
use App\Repository\Models\Reservas\ReservaHuesped;
use DomainException;

final class ValidarDocumentacionHuesped
{
    /**
     * Valida que un huésped adulto cuente con documento de identificación válido.
     *
     * @param  ReservaHuesped|array<string, mixed>  $huesped
     */
    public function validar(ReservaHuesped|array $huesped): void
    {
        $tipo = $huesped instanceof ReservaHuesped ? $huesped->tipo_huesped : ($huesped['tipo_huesped'] ?? null);
        $identificacion = $huesped instanceof ReservaHuesped ? $huesped->identificacion : ($huesped['identificacion'] ?? null);

        $esAdulto = $tipo === TipoHuesped::ADULTO || $tipo === TipoHuesped::ADULTO->value || $tipo === 'adulto';

        if ($esAdulto && (! is_string($identificacion) || trim($identificacion) === '')) {
            throw new DomainException('Todo huésped adulto debe incluir un número de identificación (DNI, Cédula o Pasaporte).');
        }
    }

    /**
     * Comprueba si la documentación de la lista de huéspedes está completa para el check-in.
     *
     * @param  iterable<ReservaHuesped>  $huespedes
     */
    public function estaCompletaParaCheckIn(iterable $huespedes): bool
    {
        foreach ($huespedes as $huesped) {
            if ($huesped->tipo_huesped === TipoHuesped::ADULTO && ($huesped->identificacion === null || trim($huesped->identificacion) === '')) {
                return false;
            }
        }

        return true;
    }
}
