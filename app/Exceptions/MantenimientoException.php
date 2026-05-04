<?php

namespace App\Exceptions;

class MantenimientoException extends HotelException
{
    public function getStatusCode(): int
    {
        return 503;
    }

    public function getTitle(): string
    {
        return 'Servicio en Mantenimiento';
    }
}
