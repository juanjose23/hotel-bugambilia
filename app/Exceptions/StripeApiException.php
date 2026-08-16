<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class StripeApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message,
        private readonly array $details = [],
        int $code = 0,
    ) {
        parent::__construct($message, $code);
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }
}
