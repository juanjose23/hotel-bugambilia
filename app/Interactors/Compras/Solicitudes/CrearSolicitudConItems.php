<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Solicitudes;

use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Compras\SolicitudRepositorioInterface;

final class CrearSolicitudConItems
{
    public function __construct(
        private readonly GenerarCodigoSolicitud $generarCodigo,
        private readonly SolicitudRepositorioInterface $solicitudRepositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, array<string, mixed>>  $items
     */
    public function ejecutar(array $datos, array $items): Solicitud
    {
        if (! isset($datos['codigo'])) {
            $rawId = $datos['departamento_solicitante_id'] ?? null;
            $departamentoId = is_numeric($rawId) ? (int) $rawId : 0;
            $datos['codigo'] = $this->generarCodigo->ejecutar($departamentoId);
        }

        return $this->solicitudRepositorio->crearConItems($datos, $items);
    }
}
