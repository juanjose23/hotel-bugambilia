<?php

declare(strict_types=1);

namespace App\Repository\Observers\Compras;

use App\Events\Compras\RecepcionCreada;
use App\Repository\Models\Compras\RecepcionCompra;
use Illuminate\Support\Facades\Log;

class RecepcionObserver
{
    public function created(RecepcionCompra $recepcion): void
    {
        Log::info("RecepcionCompra {$recepcion->codigo} registrada con éxito.");

        RecepcionCreada::dispatch($recepcion);
    }

    public function updated(RecepcionCompra $recepcion): void
    {
        if ($recepcion->isDirty('estado')) {
            Log::info("RecepcionCompra {$recepcion->codigo} cambió su estado a: {$recepcion->estado->value}");
        }
    }
}
