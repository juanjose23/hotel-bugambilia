<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Contracts\View\View;

final class ComandaController extends Controller
{
    /**
     * Muestra la vista de comanda de cocina para impresión POS térmica.
     */
    public function imprimir(Pedido $pedido): View
    {
        $this->authorize('viewComanda', $pedido);
        $pedido->loadMissing(['items.plato', 'mesa']);

        return view('restaurante.comanda', compact('pedido'));
    }
}
