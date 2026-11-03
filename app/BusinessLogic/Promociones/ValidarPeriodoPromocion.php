<?php

declare(strict_types=1);

namespace App\BusinessLogic\Promociones;

use DateTimeImmutable;
use DomainException;

final readonly class ValidarPeriodoPromocion
{
    public function ejecutar(DateTimeImmutable $entrada, ?DateTimeImmutable $salida, int $personas,
        ?int $noches, ?int $maxPersonas, int $anticipacion, DateTimeImmutable $ahora, bool $alojamientoPrecioFijo): void
    {
        if ($maxPersonas !== null && $personas > $maxPersonas) {
            throw new DomainException("La promoción incluye hasta {$maxPersonas} personas.");
        }
        if ($entrada < $ahora->modify('+'.max(0, $anticipacion).' hours')) {
            throw new DomainException("La promoción requiere al menos {$anticipacion} horas de anticipación.");
        }
        if ($alojamientoPrecioFijo && ($noches === null || $noches < 1)) {
            throw new DomainException('Esta oferta de alojamiento aún no tiene configuradas las noches incluidas. Contacta con recepción.');
        }
        if ($salida !== null && $noches !== null && (int) $entrada->setTime(0, 0)->diff($salida->setTime(0, 0))->format('%a') !== $noches) {
            throw new DomainException("La promoción incluye exactamente {$noches} noches.");
        }
    }
}
