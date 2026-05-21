<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Lotes\Mutations;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Notifications\Inventario\CaducidadProxima;
use App\Services\Inventario\NotificadorInventario;
use Illuminate\Support\Facades\Notification;

class VerificarCaducidades
{
    public function execute(): void
    {
        $this->procesarVencidos();
        $this->notificarProximos();
    }

    private function procesarVencidos(): void
    {
        $ubicacionMerma = Ubicacion::where('tipo', 'zona')
            ->where('nombre', 'like', '%merma%')
            ->orWhere('descripcion', 'like', '%merma%')
            ->first();

        Lote::whereIn('estado', [EstadoLote::Disponible, EstadoLote::Cuarentena])
            ->where('cantidad_disponible', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now()->toDateString())
            ->chunkById(200, function ($vencidos) use ($ubicacionMerma) {
                $ids = $vencidos->pluck('id')->toArray();

                Lote::whereIn('id', $ids)->update([
                    'estado' => EstadoLote::Vencido,
                    'cantidad_disponible' => 0,
                ]);

                Stock::whereIn('lote_id', $ids)->delete();

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

                    app(NotificadorInventario::class)->loteCaducado($lote);
                }

                MovimientoStock::insert($movimientos);
            });
    }

    private function notificarProximos(): void
    {
        Lote::where('estado', EstadoLote::Disponible)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('fecha_vencimiento', '<=', now()->addDays(30)->toDateString())
            ->chunkById(200, function ($proximos) {
                foreach ($proximos as $lote) {
                    $dias = now()->diffInDays($lote->fecha_vencimiento);

                    app(NotificadorInventario::class)->loteProximoACaducar($lote, (int) $dias);

                    try {
                        Notification::route('mail', config('inventario.notificaciones_email', 'admin@hotel.test'))
                            ->notify(new CaducidadProxima($lote, (int) $dias));
                    } catch (\Throwable $e) {
                        // Evitar caídas si no hay mailer configurado
                    }
                }
            });
    }
}
