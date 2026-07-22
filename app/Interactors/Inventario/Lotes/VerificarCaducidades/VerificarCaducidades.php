<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes\VerificarCaducidades;

use App\Enums\Inventario\EstadoLote;
use App\Notifications\Inventario\NotificadorInventario;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;

class VerificarCaducidades
{
    public function __construct(
        private readonly NotificadorInventario $notificador,
    ) {}

    public function execute(): void
    {
        $this->procesarVencidos();
        $this->notificarProximos();
    }

    private function procesarVencidos(): void
    {
        $ubicacionMerma = Ubicacion::query()
            ->where('tipo', 'zona')
            ->where('nombre', 'like', '%merma%')
            ->orWhere('descripcion', 'like', '%merma%')
            ->first();

        Lote::query()
            ->whereIn('estado', [EstadoLote::Disponible, EstadoLote::Cuarentena])
            ->where('cantidad_disponible', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now()->toDateString())
            ->chunkById(200, function ($vencidos) use ($ubicacionMerma) {
                $ids = $vencidos->pluck('id')->toArray();

                Lote::query()->whereIn('id', $ids)->update([
                    'estado' => EstadoLote::Vencido,
                    'cantidad_disponible' => 0,
                ]);

                Stock::query()->whereIn('lote_id', $ids)->delete();

                $movimientos = [];
                $now = now()->toDateTimeString();

                foreach ($vencidos as $lote) {
                    $movimientos[] = [
                        'tipo' => 'MOV_AJUSTE',
                        'lote_id' => $lote->id,
                        'producto_id' => $lote->producto_id,
                        'cantidad' => $lote->cantidad_disponible,
                        'ubicacion_origen_id' => $lote->ubicacion_id,
                        'ubicacion_destino_id' => $ubicacionMerma?->id,
                        'referencia' => "Vencimiento lote {$lote->codigo_lote}",
                        'created_at' => $now,
                    ];

                    $this->notificador->loteCaducado($lote);
                }

                MovimientoStock::query()->insert($movimientos);
            });
    }

    private function notificarProximos(): void
    {
        Lote::query()
            ->where('estado', EstadoLote::Disponible)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('fecha_vencimiento', '<=', now()->addDays(30)->toDateString())
            ->chunkById(200, function ($proximos) {
                foreach ($proximos as $lote) {
                    $dias = now()->diffInDays($lote->fecha_vencimiento);

                    $this->notificador->loteProximoACaducar($lote, (int) $dias);
                    // Evitar caídas si no hay mailer configurado
                }
            });
    }
}
