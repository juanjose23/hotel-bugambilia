<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Estancias\EstadoEstancia;
use App\Repository\Models\Estancias\Estancia;
use DateTimeImmutable;
use Illuminate\Support\Collection;

final class ObtenerEstanciasProximasCheckOutQuery
{
    /** @return Collection<int, Estancia> */
    public function ejecutar(DateTimeImmutable $limiteHorizonte): Collection
    {
        return Estancia::query()
            ->with(['reserva', 'habitacion'])
            ->where('estado', EstadoEstancia::ACTIVA)
            ->whereNull('fecha_check_out_real')
            ->where('fecha_salida_programada', '<=', $limiteHorizonte)
            ->get();
    }
}
