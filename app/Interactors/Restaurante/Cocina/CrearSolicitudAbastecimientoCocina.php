<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Compras\EstadoSolicitud;
use App\Notifications\Compras\NotificadorCompras;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class CrearSolicitudAbastecimientoCocina
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly NotificadorCompras $notificadorCompras,
    ) {}

    /**
     * @param  array<int, array{producto_id?: int|null, producto_variante_id?: int|null, cantidad: float|int|string, justificacion?: string|null}>  $items
     */
    public function ejecutar(
        string $motivo,
        array $items,
        ?string $fechaNecesita = null,
        ?int $colaboradorId = null
    ): Solicitud {
        return DB::transaction(function () use ($motivo, $items, $fechaNecesita, $colaboradorId): Solicitud {
            if ($colaboradorId === null || $colaboradorId <= 0) {
                throw new \DomainException('Debe existir un colaborador responsable para crear la solicitud de abastecimiento de cocina.');
            }

            $codigo = 'SOL-COC-'.now()->format('Ymd-His');

            $solicitud = $this->repositorio->crearSolicitudAbastecimiento([
                'codigo' => $codigo,
                'colaborador_id' => $colaboradorId,
                'fecha_solicitud' => now()->toDateString(),
                'fecha_necesita' => $fechaNecesita ?? now()->addDays(1)->toDateString(),
                'motivo' => $motivo,
                'estado' => EstadoSolicitud::Pendiente,
            ]);

            foreach ($items as $item) {
                $productoVarianteId = isset($item['producto_variante_id']) ? (int) $item['producto_variante_id'] : null;
                $productoId = isset($item['producto_id']) ? (int) $item['producto_id'] : null;
                $cant = (float) $item['cantidad'];
                $just = is_string($item['justificacion'] ?? null) ? $item['justificacion'] : null;

                if (($productoId === null || $productoId <= 0) && ($productoVarianteId === null || $productoVarianteId <= 0)) {
                    continue;
                }

                if ($cant <= 0) {
                    continue;
                }

                $variante = $productoVarianteId !== null && $productoVarianteId > 0
                    ? ProductoVariante::query()->with('producto')->find($productoVarianteId)
                    : null;

                if ($variante !== null) {
                    $productoId = (int) $variante->producto_id;
                }

                if ($productoId === null || $productoId <= 0) {
                    continue;
                }

                $producto = $variante !== null ? $variante->producto : $this->repositorio->obtenerProductoPorId($productoId);
                $unidadMedidaId = $variante !== null ? $variante->unidad_medida_id : $producto?->unidad_medida_id;

                $solicitud->items()->create([
                    'producto_id' => $productoId,
                    'producto_variante_id' => $variante?->id,
                    'cantidad_solicitada' => $cant,
                    'unidad_medida_id' => $unidadMedidaId,
                    'observaciones' => $just,
                ]);
            }

            $this->notificadorCompras->abastecimientoCocinaCreado($solicitud);

            return $solicitud;
        });
    }
}
