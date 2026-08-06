<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\BusinessLogic\Limpieza\Data\IniciarLimpiezaData;
use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Limpieza\Carrito\BloquearCarritoParaLimpieza;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionParaActualizar;
use Illuminate\Support\Facades\DB;

final class ReclamarEIniciarLimpieza
{
    public function __construct(
        private readonly ObtenerEjecucionParaActualizar $obtenerEjecucion,
        private readonly IniciarLimpieza $iniciarLimpieza,
        private readonly BloquearCarritoParaLimpieza $bloquearCarrito,
    ) {}

    public function execute(int $ejecucionId, int $colaboradorId, ?int $carritoId): LimpiezaEjecucion
    {
        return DB::transaction(function () use ($ejecucionId, $colaboradorId, $carritoId): LimpiezaEjecucion {
            if ($carritoId !== null) {
                $this->bloquearCarrito->execute($carritoId, $ejecucionId, $colaboradorId);
            }

            $ejecucion = $this->obtenerEjecucion->execute($ejecucionId);

            if ($ejecucion->estado !== EstadoLimpieza::Pendiente) {
                throw new OperacionLimpiezaNoPermitida('La tarea ya no está disponible para iniciar.');
            }

            if ($ejecucion->colaborador_id !== null && $ejecucion->colaborador_id !== $colaboradorId) {
                throw new OperacionLimpiezaNoPermitida('La tarea ya fue asignada a otro colaborador.');
            }

            $this->iniciarLimpieza->execute(new IniciarLimpiezaData(
                record: $ejecucion,
                colaboradorOrPersonalId: $colaboradorId,
                carritoId: $carritoId,
            ));

            return $ejecucion->refresh();
        });
    }
}
