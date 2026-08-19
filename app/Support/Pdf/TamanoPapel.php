<?php

declare(strict_types=1);

namespace App\Support\Pdf;

enum TamanoPapel
{
    case A4;
    case LETTER;
    case LEGAL;

    public function anchoMm(): int
    {
        return match ($this) {
            self::A4 => 210,
            self::LETTER => 216,
            self::LEGAL => 216,
        };
    }

    public function altoMm(): int
    {
        return match ($this) {
            self::A4 => 297,
            self::LETTER => 279,
            self::LEGAL => 356,
        };
    }

    public function dompdfName(): string
    {
        return match ($this) {
            self::A4 => 'a4',
            self::LETTER => 'letter',
            self::LEGAL => 'legal',
        };
    }

    public function cssName(): string
    {
        return match ($this) {
            self::A4 => 'a4',
            self::LETTER => 'letter',
            self::LEGAL => 'legal',
        };
    }

    public static function fromRequest(?string $tamano): self
    {
        return match (strtolower((string) $tamano)) {
            'a4' => self::A4,
            'legal' => self::LEGAL,
            default => self::LETTER,
        };
    }
}
