<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Compras\EstadoSolicitud;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class CrearSolicitudAbastecimientoCocina
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<int, array{producto_id: int, cantidad: float, justificacion?: string|null}>  $items
     */
    public function ejecutar(
        string $motivo,
        array $items,
        ?string $fechaNecesita = null,
        ?int $usuarioId = null
    ): Solicitud {
        return DB::transaction(function () use ($motivo, $items, $fechaNecesita, $usuarioId): Solicitud {
            $codigo = 'SOL-COC-'.now()->format('Ymd-His');

            $solicitud = $this->repositorio->crearSolicitudAbastecimiento([
                'codigo' => $codigo,
                'fecha_solicitud' => now()->toDateString(),
                'fecha_necesita' => $fechaNecesita ?? now()->addDays(1)->toDateString(),
                'motivo' => $motivo,
                'estado' => EstadoSolicitud::Pendiente,
                'creado_por' => $usuarioId,
            ]);

            foreach ($items as $item) {
                $productoId = $item['producto_id'];
                $cant = $item['cantidad'];
                $just = is_string($item['justificacion'] ?? null) ? $item['justificacion'] : null;

                if ($productoId <= 0 || $cant <= 0) {
                    continue;
                }

                $producto = $this->repositorio->obtenerProductoPorId($productoId);
                $unidadMedidaId = $producto !== null ? $producto->unidad_medida_id : null;

                $solicitud->items()->create([
                    'producto_id' => $productoId,
                    'cantidad_solicitada' => $cant,
                    'unidad_medida_id' => $unidadMedidaId,
                    'justificacion' => $just,
                ]);
            }

            return $solicitud;
        });
    }
}
