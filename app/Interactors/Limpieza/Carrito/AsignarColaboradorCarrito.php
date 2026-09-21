<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Enums\Catalogos\TipoUbicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Persistencia\Catalogos\UbicacionRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use Carbon\Carbon;
use InvalidArgumentException;
use RuntimeException;

final readonly class AsignarColaboradorCarrito
{
    public function __construct(
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
        private UbicacionRepositorioInterface $ubicacionRepositorio,
    ) {}

    /**
     * Asigna un colaborador a un carro físico para una fecha (por defecto hoy).
     */
    public function execute(int $colaboradorId, int $carritoId, ?string $fecha = null): LimpiezaEjecucion
    {
        return $this->ejecutar($colaboradorId, $carritoId, $fecha);
    }

    public function ejecutar(int $colaboradorId, int $carritoId, ?string $fecha = null): LimpiezaEjecucion
    {
        $fecha = $fecha ?: Carbon::now()->toDateString();

        $carrito = $this->ubicacionRepositorio->buscarPorId($carritoId);
        if (! $carrito) {
            throw new InvalidArgumentException("La ubicación con ID {$carritoId} no existe.");
        }

        $tipo = $carrito->tipo;
        if ($tipo !== TipoUbicacion::CARRITO->value && $tipo !== 'carrito') {
            throw new InvalidArgumentException("La ubicación con ID {$carritoId} no es de tipo 'carrito'.");
        }

        $existingCartAsg = $this->limpiezaRepositorio->buscarEjecucionPorCarritoFecha($carritoId, $fecha);
        if ($existingCartAsg && (int) $existingCartAsg->colaborador_id !== $colaboradorId) {
            throw new RuntimeException("El carrito seleccionado ya está asignado en otra limpieza activa para la fecha {$fecha}.");
        }

        $ejecucion = $this->limpiezaRepositorio->buscarEjecucionPorColaboradorFecha($colaboradorId, $fecha);
        if (! $ejecucion) {
            throw new RuntimeException('No se encontró una tarea de limpieza activa o pendiente para el colaborador hoy.');
        }

        $this->limpiezaRepositorio->actualizarEjecucion($ejecucion, [
            'carrito_id' => $carritoId,
        ]);

        return $ejecucion;
    }
}
