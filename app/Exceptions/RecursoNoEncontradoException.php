<?php

declare(strict_types=1);

namespace App\Exceptions;

class RecursoNoEncontradoException extends HotelException
{
    public function getStatusCode(): int
    {
        return 404;
    }

    public function getTitle(): string
    {
        return 'Elemento no Encontrado';
    }
}
