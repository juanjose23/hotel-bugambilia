<?php

declare(strict_types=1);

namespace App\Support\Pdf;

enum FormatoPagina: string
{
    case A4_Vertical = 'a4_vertical';
    case A4_Horizontal = 'a4_horizontal';
    case Carta_Vertical = 'carta_vertical';
    case Carta_Horizontal = 'carta_horizontal';
    case Legal_Vertical = 'legal_vertical';
    case Legal_Horizontal = 'legal_horizontal';

    public function label(): string
    {
        return match ($this) {
            self::A4_Vertical => 'A4 Vertical',
            self::A4_Horizontal => 'A4 Horizontal',
            self::Carta_Vertical => 'Carta Vertical',
            self::Carta_Horizontal => 'Carta Horizontal',
            self::Legal_Vertical => 'Legal Vertical',
            self::Legal_Horizontal => 'Legal Horizontal',
        };
    }

    /**
     * @return array{0: TamanoPapel, 1: Orientacion}
     */
    public function resolver(): array
    {
        return match ($this) {
            self::A4_Vertical => [TamanoPapel::A4, Orientacion::Vertical],
            self::A4_Horizontal => [TamanoPapel::A4, Orientacion::Horizontal],
            self::Carta_Vertical => [TamanoPapel::LETTER, Orientacion::Vertical],
            self::Carta_Horizontal => [TamanoPapel::LETTER, Orientacion::Horizontal],
            self::Legal_Vertical => [TamanoPapel::LEGAL, Orientacion::Vertical],
            self::Legal_Horizontal => [TamanoPapel::LEGAL, Orientacion::Horizontal],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
