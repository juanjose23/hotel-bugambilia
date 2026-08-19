<?php

declare(strict_types=1);

namespace App\Support\Pdf;

enum Orientacion
{
    case Vertical;
    case Horizontal;

    public function altoPaginaMm(TamanoPapel $tamano): int
    {
        return match ($this) {
            self::Vertical => $tamano->altoMm(),
            self::Horizontal => $tamano->anchoMm(),
        };
    }

    public function dompdfName(): string
    {
        return match ($this) {
            self::Vertical => 'portrait',
            self::Horizontal => 'landscape',
        };
    }

    public function cssName(): string
    {
        return $this->dompdfName();
    }

    public function label(): string
    {
        return match ($this) {
            self::Vertical => 'Vertical',
            self::Horizontal => 'Horizontal',
        };
    }

    public static function fromRequest(?string $orientacion): self
    {
        return match (strtolower((string) $orientacion)) {
            'horizontal', 'landscape' => self::Horizontal,
            default => self::Vertical,
        };
    }
}
