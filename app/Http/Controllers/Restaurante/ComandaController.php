<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Enums\Restaurante\AreaCocina;
use App\Enums\Restaurante\TipoTicketComanda;
use App\Http\Controllers\Controller;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ComandaController extends Controller
{
    /**
     * Muestra la vista de comanda de cocina para impresión POS térmica.
     */
    public function imprimir(Request $request, Pedido $pedido): View
    {
        $this->authorize('viewComanda', $pedido);

        $pedido->loadMissing([
            'items.plato',
            'mesa',
            'mesero.persona',
            'cliente',
            'cuenta.estancia.habitacion',
        ]);

        $areaParam = $request->query('area');
        $tipoParam = $request->query('tipo', 'nuevo');

        $area = is_string($areaParam) ? AreaCocina::tryFrom($areaParam) : null;
        $tipo = is_string($tipoParam) ? TipoTicketComanda::tryFrom($tipoParam) ?? TipoTicketComanda::NUEVO : TipoTicketComanda::NUEVO;

        $items = $pedido->items;
        if ($area !== null) {
            $items = $items->filter(fn ($item) => $item->area_cocina === $area || $item->plato?->area_cocina === $area);
        }

        return view('reports.restaurante.comanda', [
            'pedido' => $pedido,
            'items' => $items,
            'area' => $area,
            'tipo' => $tipo,
        ]);
    }

    /**
     * Muestra la pantalla pública standalone de turnos para TV.
     */
    public function pantallaTurnosPublica(): View
    {
        return view('filament.resources.restaurante.pantalla-turnos-publica');
    }
}
