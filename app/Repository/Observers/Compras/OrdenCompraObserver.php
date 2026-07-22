<?php

declare(strict_types=1);

namespace App\Repository\Observers\Compras;

use App\Repository\Models\Compras\OrdenCompra;
use Illuminate\Support\Facades\Log;

class OrdenCompraObserver
{
    public function created(OrdenCompra $ordenCompra): void
    {
        Log::info("OrdenCompra {$ordenCompra->codigo} registrada con éxito.");
    }

    public function updated(OrdenCompra $ordenCompra): void
    {
        if ($ordenCompra->isDirty('estado')) {
            Log::info("OrdenCompra {$ordenCompra->codigo} cambió su estado a: {$ordenCompra->estado->value}");
        }
    }
}
