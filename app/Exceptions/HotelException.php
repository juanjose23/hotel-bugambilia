<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class HotelException extends Exception implements HttpExceptionInterface
{
    /**
     * Get the HTTP status code.
     */
    abstract public function getStatusCode(): int;

    /**
     * Get the user-friendly title.
     */
    abstract public function getTitle(): string;

    /**
     * Get the headers.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }
}
