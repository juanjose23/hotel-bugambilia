<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Lotes;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Queries\Inventario\Stock\ObtenerValorizacionInventario;

class ObtenerEstadisticasLotes
{
    public function __construct(
        private readonly Lote $lote,
        private readonly Moneda $moneda,
        private readonly ObtenerValorizacionInventario $valorizacionInventario,
    ) {}

    /** @return array{lotes_en_stock: int, lotes_cuarentena: int, lotes_proximos_vencer: int, valor_total: float, simbolo_moneda: string} */
    public function execute(): array
    {
        $lotesEnStock = $this->loadLotesEnStock();
        $lotesCuarentena = $this->loadLotesEnCuarentena();
        $lotesProximosVencer = $this->loadLotesProximosVencer();
        $valorTotal = $this->loadValorTotal();
        $simbolo = $this->loadSimboloMonedaBase();

        return [
            'lotes_en_stock' => $lotesEnStock,
            'lotes_cuarentena' => $lotesCuarentena,
            'lotes_proximos_vencer' => $lotesProximosVencer,
            'valor_total' => $valorTotal,
            'simbolo_moneda' => $simbolo,
        ];
    }

    private function loadLotesEnStock(): int
    {
        return $this->lote->query()
            ->where('estado', EstadoLote::Disponible)
            ->where('cantidad_disponible', '>', 0)
            ->count();
    }

    private function loadLotesEnCuarentena(): int
    {
        return $this->lote->query()
            ->where('estado', EstadoLote::Cuarentena)
            ->count();
    }

    private function loadLotesProximosVencer(): int
    {
        return $this->lote->query()
            ->where('estado', EstadoLote::Disponible)
            ->where('cantidad_disponible', '>', 0)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->count();
    }

    private function loadValorTotal(): float
    {
        return $this->valorizacionInventario->totalGeneral();
    }

    private function loadSimboloMonedaBase(): string
    {
        $moneda = $this->moneda->query()->where('es_predeterminada', true)->first();

        return $moneda->simbolo ?? 'C$';
    }
}
