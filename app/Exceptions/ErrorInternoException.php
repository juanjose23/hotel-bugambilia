<?php

declare(strict_types=1);

namespace App\Exceptions;

class ErrorInternoException extends HotelException
{
    public function getStatusCode(): int
    {
        return 500;
    }

    public function getTitle(): string
    {
        return 'Error de Sistema';
    }
}
