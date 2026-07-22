<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\BusinessLogic\Habitaciones\ServicioAsignacionPacks;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class AsignarPackAHabitacion
{
    public function __construct(
        private readonly ServicioAsignacionPacks $servicioAsignacion,
    ) {}

    /**
     * Asigna un pack/kit a un destino genérico (habitación, espacio común o ubicación/carro).
     * Si falta stock de algún componente, surte únicamente la cantidad disponible, dejando
     * la reposición como pendiente (actual < ideal).
     *
     * @return array<int, array<string, mixed>>
     */
    public function execute(
        int $destinoId,
        int $productoPackId,
        int $bodegaOrigenId,
        float $cantidadPacks = 1.0,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        string $destinoTipo = 'habitacion',
        ?int $colaboradorId = null,
    ): array {
        if ($cantidadPacks <= 0) {
            throw new \InvalidArgumentException('La cantidad de packs debe ser mayor a cero.');
        }

        if (! in_array($destinoTipo, ['habitacion', 'espacio', 'ubicacion'], true)) {
            throw new \InvalidArgumentException("Tipo de destino inválido: {$destinoTipo}");
        }

        return DB::transaction(fn () => $this->servicioAsignacion->asignar(
            $destinoId,
            $productoPackId,
            $bodegaOrigenId,
            $cantidadPacks,
            $creadoPorId,
            $referencia,
            $destinoTipo,
            $colaboradorId
        ));
    }
}
