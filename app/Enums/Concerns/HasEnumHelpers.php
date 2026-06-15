<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

trait HasEnumHelpers
{
    public function label(): string
    {
        return $this instanceof HasLabel ? $this->getLabel() ?? $this->name : $this->name;
    }

    /**
     * @return string|array<mixed>|null
     */
    public function color(): string|array|null
    {
        return $this instanceof HasColor ? $this->getColor() : null;
    }

    public function icon(): \BackedEnum|string|null
    {
        return $this instanceof HasIcon ? $this->getIcon() : null;
    }

    /**
     * @return array<string|int, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case instanceof HasLabel ? ($case->getLabel() ?? (string) $case->value) : (string) $case->value;
        }

        return $options;
    }

    public static function fromValue(self|int|string|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }
        if ($value === null || $value === '') {
            return null;
        }

        $backingType = (new \ReflectionEnum(static::class))->getBackingType()?->getName();
        if ($backingType === 'int') {
            return self::tryFrom((int) $value);
        }

        return self::tryFrom((string) $value);
    }

    public static function labelFor(self|int|string|null $value): string
    {
        $instance = self::fromValue($value);

        if ($instance instanceof HasLabel) {
            return $instance->getLabel() ?? 'No definido';
        }

        return 'No definido';
    }

    /**
     * @return string|array<mixed>|null
     */
    public static function colorFor(self|int|string|null $value): string|array|null
    {
        $instance = self::fromValue($value);

        if ($instance instanceof HasColor) {
            return $instance->getColor() ?? 'gray';
        }

        return 'gray';
    }

    public static function iconFor(self|int|string|null $value): \BackedEnum|string|null
    {
        $instance = self::fromValue($value);

        if ($instance instanceof HasIcon) {
            return $instance->getIcon();
        }

        return null;
    }
}
