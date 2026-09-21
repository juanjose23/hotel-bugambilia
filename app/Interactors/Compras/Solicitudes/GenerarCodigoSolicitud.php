<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Solicitudes;

use App\Repository\Persistencia\Compras\SolicitudRepositorioInterface;

final readonly class GenerarCodigoSolicitud
{
    public function __construct(
        private SolicitudRepositorioInterface $solicitudRepositorio,
    ) {}

    public function ejecutar(int $departamentoId): string
    {
        $codigoDepto = $this->solicitudRepositorio->obtenerCodigoDepartamento($departamentoId) ?? 'GRAL';
        $siglas = $this->obtenerSiglas($codigoDepto);
        $prefijo = "S-{$siglas}-";

        $ultimo = $this->solicitudRepositorio->obtenerUltimoCodigoPorPrefijo($prefijo);

        $numero = $ultimo
            ? intval(substr($ultimo, strlen($prefijo))) + 1
            : 1;

        return "{$prefijo}".str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
    }

    private function obtenerSiglas(string $codigoCatalogo): string
    {
        $partes = explode('_', $codigoCatalogo);

        if (count($partes) === 1) {
            return strtoupper(substr($codigoCatalogo, 0, 4));
        }

        $sinPrefijo = array_slice($partes, 1);
        $siglas = '';

        foreach ($sinPrefijo as $palabra) {
            $siglas .= substr((string) $palabra, 0, 1);
        }

        return strtoupper($siglas);
    }
}
