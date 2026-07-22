<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

enum CategoriaPlato: string
{
    case Entradas = 'REST_ENTRADAS';
    case Platos = 'REST_PLATOS';
    case Postres = 'REST_POSTRES';
    case Bebidas = 'REST_BEBIDAS';
    case General = 'RESTAURANTE';

    public function label(): string
    {
        return match ($this) {
            self::Entradas => 'Entradas',
            self::Platos => 'Platos Fuertes',
            self::Postres => 'Postres',
            self::Bebidas => 'Bebidas',
            self::General => 'General',
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

    /**
     * @return array<int, string>
     */
    public static function codigos(): array
    {
        return array_map(
            fn (self $case) => $case->value,
            self::cases()
        );
    }
}
