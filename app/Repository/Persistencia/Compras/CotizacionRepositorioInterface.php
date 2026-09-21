<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoCotizacion;
use App\Repository\Models\Compras\Cotizacion;
use Illuminate\Support\Collection;

interface CotizacionRepositorioInterface
{
    public function buscarPorId(int $id): Cotizacion;

    /** @param list<string> $relaciones */
    public function buscarPorIdConRelaciones(int $id, array $relaciones = []): Cotizacion;

    /** @return Collection<int, Cotizacion> */
    public function obtenerGanadorasPorSolicitud(int $solicitudId): Collection;

    public function marcarGanadora(Cotizacion $cotizacion, int $solicitudId, ?int $userId): void;

    public function seleccionarItemGanador(Cotizacion $cotizacion, int $solicitudId, int $productoId, ?int $userId): void;

    public function actualizarEstado(Cotizacion $cotizacion, EstadoCotizacion $estado, bool $esElegida): void;

    public function rechazar(Cotizacion $cotizacion, string $motivo): void;
}
