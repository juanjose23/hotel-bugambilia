<?php

declare(strict_types=1);

namespace App\Notifications\Reservas;

use App\Filament\Resources\Reservas\ReservaResource;
use App\Notifications\Reservas\Contracts\UrlNotificadorInterface;
use App\Repository\Models\Reservas\Reserva;

final class UrlNotificador implements UrlNotificadorInterface
{
    public function reserva(Reserva $reserva): string
    {
        try {
            return ReservaResource::getUrl('view', ['record' => $reserva->id]);
        } catch (\Throwable) {
            return '#';
        }
    }
}
