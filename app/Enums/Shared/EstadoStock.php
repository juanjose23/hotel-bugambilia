<?php

declare(strict_types=1);

namespace App\Enums\Shared;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoStock: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Completo = 'completo';
    case Excedente = 'excedente';
    case Faltante = 'faltante';
    case Critico = 'critico';
    case Vacio = 'vacio';

    public static function calcular(float $actual, float $ideal): self
    {
        if ($ideal <= 0) {
            return $actual > 0 ? self::Excedente : self::Completo;
        }

        if ($actual <= 0) {
            return self::Vacio;
        }

        if ($actual > $ideal) {
            return self::Excedente;
        }

        if ($actual === $ideal) {

            return self::Completo;
        }

        if ($actual <= $ideal * 0.25) {
            return self::Critico;
        }

        return self::Faltante;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Completo => 'Completo',
            self::Excedente => 'Excedente',
            self::Faltante => 'Faltante',
            self::Critico => 'Crítico',
            self::Vacio => 'Vacío',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Completo => 'success',
            self::Excedente => 'info',
            self::Faltante => 'warning',
            self::Critico => 'danger',
            self::Vacio => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Completo => 'heroicon-m-check-circle',
            self::Excedente => 'heroicon-m-arrow-trending-up',
            self::Faltante => 'heroicon-m-exclamation-triangle',
            self::Critico => 'heroicon-m-x-circle',
            self::Vacio => 'heroicon-m-trash',
        };
    }
}
