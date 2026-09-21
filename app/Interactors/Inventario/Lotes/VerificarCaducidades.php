<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes;

use App\Notifications\Inventario\NotificadorInventario;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Persistencia\Catalogos\UbicacionRepositorioInterface;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class VerificarCaducidades
{
    public function __construct(
        private NotificadorInventario $notificador,
        private LoteRepositorioInterface $loteRepositorio,
        private StockRepositorioInterface $stockRepositorio,
        private MovimientoStockRepositorioInterface $movimientoStockRepositorio,
        private UbicacionRepositorioInterface $ubicacionRepositorio,
    ) {}

    public function execute(): void
    {
        $this->ejecutar();
    }

    public function ejecutar(): void
    {
        $this->procesarVencidos();
        $this->notificarProximos();
    }

    private function procesarVencidos(): void
    {
        $ubicacionMerma = $this->ubicacionRepositorio->buscarMerma();

        $this->loteRepositorio->procesarVencidosChunk(function (Collection $vencidos) use ($ubicacionMerma): void {
            /** @var array<int, int> $ids */
            $ids = $vencidos->pluck('id')->all();

            $this->loteRepositorio->marcarComoVencidos($ids);
            $this->stockRepositorio->eliminarPorLoteIds($ids);

            $movimientos = [];
            $now = now()->toDateTimeString();

            /** @var Lote $lote */
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

            if (! empty($movimientos)) {
                $this->movimientoStockRepositorio->insertarMuchos($movimientos);
            }
        });
    }

    private function notificarProximos(): void
    {
        $this->loteRepositorio->procesarProximosAVencerChunk(30, function (Collection $proximos): void {
            /** @var Lote $lote */
            foreach ($proximos as $lote) {
                if ($lote->fecha_vencimiento !== null) {
                    $dias = now()->diffInDays($lote->fecha_vencimiento);
                    $this->notificador->loteProximoACaducar($lote, (int) $dias);
                }
            }
        });
    }
}
