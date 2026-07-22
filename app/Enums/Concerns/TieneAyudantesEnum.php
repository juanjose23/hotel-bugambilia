<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

trait TieneAyudantesEnum
{
    public function etiqueta(): string
    {
        return $this instanceof HasLabel ? ($this->getLabel() ?: $this->name) : $this->name;
    }

    public function label(): string
    {
        return $this->etiqueta();
    }

    /** @return array<array-key, string>|string|null */
    public function color(): string|array|null
    {
        return $this instanceof HasColor ? $this->getColor() : null;
    }

    public function icono(): \BackedEnum|string|null
    {
        return $this instanceof HasIcon ? $this->getIcon() : null;
    }

    public function icon(): \BackedEnum|string|null
    {
        return $this->icono();
    }

    /** @return array<array-key, string> */
    public static function opciones(): array
    {
        $opciones = [];
        foreach (self::cases() as $caso) {
            $opciones[$caso->value] = $caso->etiqueta();
        }

        return $opciones;
    }

    /** @return array<array-key, string> */
    public static function options(): array
    {
        return self::opciones();
    }

    public static function desdeValor(self|int|string|null $valor): ?self
    {
        if ($valor instanceof self) {
            return $valor;
        }
        if ($valor === null || $valor === '') {
            return null;
        }

        $backingType = (new \ReflectionEnum(static::class))->getBackingType()?->getName();
        if ($backingType === 'int') {
            return self::tryFrom((int) $valor);
        }

        return self::tryFrom($valor);
    }

    public static function fromValue(self|int|string|null $value): ?self
    {
        return self::desdeValor($value);
    }

    public static function etiquetaPara(self|int|string|null $valor): string
    {
        $instancia = self::desdeValor($valor);

        if ($instancia instanceof HasLabel) {
            return $instancia->getLabel() ?: 'No definido';
        }

        return 'No definido';
    }

    public static function labelFor(self|int|string|null $value): string
    {
        return self::etiquetaPara($value);
    }

    /** @return array<array-key, string>|string|null */
    public static function colorPara(self|int|string|null $valor): string|array|null
    {
        $instancia = self::desdeValor($valor);

        if ($instancia instanceof HasColor) {
            return $instancia->getColor() ?: 'gray';
        }

        return 'gray';
    }

    /** @return array<array-key, string>|string|null */
    public static function colorFor(self|int|string|null $value): string|array|null
    {
        return self::colorPara($value);
    }

    public static function iconoPara(self|int|string|null $valor): \BackedEnum|string|null
    {
        $instancia = self::desdeValor($valor);

        if ($instancia instanceof HasIcon) {
            return $instancia->getIcon();
        }

        return null;
    }

    public static function iconFor(self|int|string|null $value): \BackedEnum|string|null
    {
        return self::iconoPara($value);
    }
}
