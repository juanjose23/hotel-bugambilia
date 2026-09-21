<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoCotizacion;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\CotizacionItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class CotizacionRepositorio implements CotizacionRepositorioInterface
{
    public function buscarPorId(int $id): Cotizacion
    {
        /** @var Cotizacion $cotizacion */
        $cotizacion = Cotizacion::query()->findOrFail($id);

        return $cotizacion;
    }

    /** @param list<string> $relaciones */
    public function buscarPorIdConRelaciones(int $id, array $relaciones = []): Cotizacion
    {
        /** @var Cotizacion $cotizacion */
        $cotizacion = Cotizacion::with($relaciones)->findOrFail($id);

        return $cotizacion;
    }

    /** @return Collection<int, Cotizacion> */
    public function obtenerGanadorasPorSolicitud(int $solicitudId): Collection
    {
        return Cotizacion::where('solicitud_id', $solicitudId)
            ->with(['items' => fn ($q) => $q->where('es_elegido', true)])
            ->whereHas('items', fn ($q) => $q->where('es_elegido', true))
            ->get();
    }

    public function marcarGanadora(Cotizacion $cotizacion, int $solicitudId, ?int $userId): void
    {
        Cotizacion::where('solicitud_id', $solicitudId)->update([
            'es_elegida' => false,
            'elegida_por' => null,
            'elegida_en' => null,
        ]);

        DB::table('cotizacion_items')
            ->whereIn('cotizacion_id', function ($query) use ($solicitudId) {
                $query->select('id')->from('cotizaciones')->where('solicitud_id', $solicitudId);
            })
            ->update(['es_elegido' => false]);

        $cotizacion->update([
            'es_elegida' => true,
            'elegida_por' => $userId,
            'elegida_en' => now(),
        ]);

        $cotizacion->items()->update(['es_elegido' => true]);
    }

    public function seleccionarItemGanador(Cotizacion $cotizacion, int $solicitudId, int $productoId, ?int $userId): void
    {
        CotizacionItem::whereIn('cotizacion_id', function ($query) use ($solicitudId) {
            $query->select('id')->from('cotizaciones')->where('solicitud_id', $solicitudId);
        })
            ->where('producto_id', $productoId)
            ->update(['es_elegido' => false]);

        CotizacionItem::where('cotizacion_id', $cotizacion->id)
            ->where('producto_id', $productoId)
            ->update(['es_elegido' => true]);

        if (! $cotizacion->elegida_por) {
            $cotizacion->update([
                'elegida_por' => $userId,
                'elegida_en' => now(),
            ]);
        }
    }

    public function actualizarEstado(Cotizacion $cotizacion, EstadoCotizacion $estado, bool $esElegida): void
    {
        $cotizacion->update([
            'estado' => $estado,
            'es_elegida' => $esElegida,
        ]);
    }

    public function rechazar(Cotizacion $cotizacion, string $motivo): void
    {
        $cotizacion->update([
            'estado' => EstadoCotizacion::Rechazada,
            'motivo_rechazo' => $motivo,
        ]);
    }
}
