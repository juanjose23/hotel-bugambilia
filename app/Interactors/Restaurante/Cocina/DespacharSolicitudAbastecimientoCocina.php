<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Compras\EstadoSolicitud;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Compras\SolicitudItem;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class DespacharSolicitudAbastecimientoCocina
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private TrasladarExistenciasCocina $trasladar,
    ) {}

    public function ejecutar(
        Solicitud $solicitud,
        int $ubicacionOrigenId,
        ?int $ubicacionDestinoId = null,
        ?int $usuarioId = null,
    ): Solicitud {
        return DB::transaction(function () use ($solicitud, $ubicacionOrigenId, $ubicacionDestinoId, $usuarioId): Solicitud {
            $solicitud = $this->repositorio->solicitudConLock($solicitud->id);
            if ($this->repositorio->solicitudYaAbastecida($solicitud->id)) {
                throw new DomainException('La solicitud ya fue abastecida; no se puede repetir el despacho.');
            }
            $solicitud->load(['items.producto', 'items.variante']);

            if ($solicitud->estado !== EstadoSolicitud::Aprobada) {
                throw new DomainException('Solo se puede despachar una solicitud aprobada.');
            }

            $this->validarAutorizacionInventario($usuarioId);

            $destinoId = $ubicacionDestinoId ?? $this->repositorio->obtenerUbicacionCocinaId();
            $origen = $this->repositorio->obtenerUbicacionPorId($ubicacionOrigenId);
            $destino = $this->repositorio->obtenerUbicacionPorId($destinoId);

            if (! $origen instanceof Ubicacion || ! $destino instanceof Ubicacion) {
                throw new DomainException('Seleccione ubicación origen y destino válidas.');
            }

            foreach ($solicitud->items as $item) {
                $this->despacharItem($solicitud, $item, (int) $origen->id, (int) $destino->id, $usuarioId);
            }

            $nota = '['.now()->format('d/m/Y H:i')."] DESPACHO COCINA: {$origen->nombre} -> {$destino->nombre}";
            $solicitud->notas = trim((string) ($solicitud->notas ?? '')."\n".$nota);
            $this->repositorio->guardarSolicitudAbastecimiento($solicitud);

            return $solicitud->refresh();
        });
    }

    private function validarAutorizacionInventario(?int $usuarioId): void
    {
        $usuario = $usuarioId !== null ? $this->repositorio->obtenerUsuarioPorId($usuarioId) : null;

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

        $this->trasladar->ejecutar($varianteId, $origenId, $destinoId, $cantidad, ['documento_tipo' => 'solicitud_abastecimiento_cocina', 'documento_id' => $solicitud->id, 'referencia' => 'Despacho de '.$solicitud->codigo, 'creado_por_id' => $usuarioId]);
    }
}
