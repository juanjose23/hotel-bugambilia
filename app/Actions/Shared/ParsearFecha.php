<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final class ParsearFecha
{
    public function ejecutar(?string $fecha, CarbonInterface $default): CarbonInterface
    {
        if ($fecha === null || trim($fecha) === '') {
            return $default;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $fecha);

            return $parsed instanceof Carbon ? $parsed : $default;
        } catch (\Exception) {
            return $default;
        }
    }
}
