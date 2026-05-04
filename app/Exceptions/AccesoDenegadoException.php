<?php

namespace App\Exceptions;

class AccesoDenegadoException extends HotelException
{
    public function getStatusCode(): int
    {
        return 403;
    }

    public function getTitle(): string
    {
        return 'Acceso Restringido';
    }
}
