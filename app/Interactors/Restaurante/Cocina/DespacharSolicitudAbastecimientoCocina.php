<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Compras\EstadoSolicitud;
use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Compras\SolicitudItem;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class DespacharSolicitudAbastecimientoCocina
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(
        Solicitud $solicitud,
        int $ubicacionOrigenId,
        ?int $ubicacionDestinoId = null,
        ?int $usuarioId = null,
    ): Solicitud {
        return DB::transaction(function () use ($solicitud, $ubicacionOrigenId, $ubicacionDestinoId, $usuarioId): Solicitud {
            $solicitud->loadMissing(['items.producto', 'items.variante']);

            if ($solicitud->estado !== EstadoSolicitud::Aprobada) {
                throw new DomainException('Solo se puede despachar una solicitud aprobada.');
            }

            $this->validarAutorizacionInventario($usuarioId);

            $destinoId = $ubicacionDestinoId ?? $this->obtenerUbicacionCocinaId();
            $origen = Ubicacion::query()->find($ubicacionOrigenId);
            $destino = Ubicacion::query()->find($destinoId);

            if (! $origen instanceof Ubicacion || ! $destino instanceof Ubicacion) {
                throw new DomainException('Seleccione ubicación origen y destino válidas.');
            }

            foreach ($solicitud->items as $item) {
                $this->despacharItem($solicitud, $item, (int) $origen->id, (int) $destino->id, $usuarioId);
            }

            $nota = '['.now()->format('d/m/Y H:i')."] DESPACHO COCINA: {$origen->nombre} -> {$destino->nombre}";
            $solicitud->notas = trim((string) ($solicitud->notas ?? '')."\n".$nota);
            $solicitud->save();

            return $solicitud->refresh();
        });
    }

    private function validarAutorizacionInventario(?int $usuarioId): void
    {
        $usuario = $usuarioId !== null ? User::query()->find($usuarioId) : null;

        if (! $usuario instanceof User || ! $usuario->can('Inventario:ResolverAbastecimientoCocina')) {
            throw new DomainException('Debe autorizar este despacho un usuario con permiso de inventario.');
        }
    }

    private function despacharItem(Solicitud $solicitud, SolicitudItem $item, int $origenId, int $destinoId, ?int $usuarioId): void
    {
        $varianteId = (int) ($item->producto_variante_id ?? 0);
        $productoId = (int) ($item->producto_id ?? 0);
        $cantidad = (float) ($item->cantidad_aprobada ?? 0);

        if ($cantidad <= 0) {
            $cantidad = (float) $item->cantidad_solicitada;
        }

        if ($varianteId <= 0 || $productoId <= 0 || $cantidad <= 0) {
            return;
        }

        $stockOrigen = $this->repositorio->obtenerStockPorVariante($origenId, $varianteId);

        if (! $stockOrigen instanceof Stock) {
            throw new DomainException('No existe stock registrado para el insumo solicitado.');
        }

        $disponible = (float) $stockOrigen->cantidad_actual;

        if ($disponible < $cantidad) {
            $nombre = $item->variante !== null ? ($item->variante->producto->nombre ?? 'Producto') : ($item->producto->nombre ?? 'Producto');
            $variante = $item->variante !== null ? ($item->variante->nombre_variante ?: $item->variante->codigo) : null;
            $label = $variante !== null ? "{$nombre} - {$variante}" : $nombre;

            throw new DomainException("Stock insuficiente en bodega para {$label}. Disponible: {$disponible}; requerido: {$cantidad}.");
        }

        $stockOrigen->cantidad_actual = $disponible - $cantidad;
        $this->repositorio->guardarStock($stockOrigen);

        $stockDestino = $this->repositorio->obtenerStockPorVariante($destinoId, $varianteId);

        if (! $stockDestino instanceof Stock) {
            $stockDestino = Stock::query()->create([
                'stockable_type' => Ubicacion::class,
                'stockable_id' => $destinoId,
                'producto_variante_id' => $varianteId,
                'lote_id' => $stockOrigen->lote_id,
                'cantidad_ideal' => 0,
                'cantidad_actual' => 0,
            ]);
        }

        $stockDestino->cantidad_actual = (float) $stockDestino->cantidad_actual + $cantidad;
        $this->repositorio->guardarStock($stockDestino);

        $costoUnitario = $stockOrigen->lote?->costo_unitario;

        $this->repositorio->registrarMovimiento([
            'tipo' => 'TRASLADO',
            'lote_id' => $stockOrigen->lote_id,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoUnitario !== null ? (float) $costoUnitario * $cantidad : null,
            'ubicacion_origen_id' => $origenId,
            'ubicacion_destino_id' => $destinoId,
            'documento_tipo' => 'solicitud_abastecimiento_cocina',
            'documento_id' => $solicitud->id,
            'referencia' => "Despacho de solicitud {$solicitud->codigo} hacia cocina",
            'creado_por_id' => $usuarioId,
        ]);
    }

    private function obtenerUbicacionCocinaId(): int
    {
        $id = Ubicacion::query()
            ->where('nombre', UbicacionCocina::RESTAURANTE->value)
            ->orWhere('nombre', 'Cocina')
            ->orWhere('nombre', 'like', '%Cocina%')
            ->orWhere('tipo', 'cocina')
            ->value('id');

        if (! is_numeric($id)) {
            throw new DomainException('No existe una ubicación de cocina configurada para recibir abastecimiento.');
        }

        return (int) $id;
    }
}
