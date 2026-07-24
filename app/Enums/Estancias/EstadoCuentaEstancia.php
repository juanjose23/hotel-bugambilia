<?php

declare(strict_types=1);

namespace App\Enums\Estancias;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoCuentaEstancia: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case SOLICITADA = 1;
    case ABIERTA = 2;
    case BLOQUEADA = 3;
    case PENDIENTE_DE_PAGO = 4;
    case CERRADA = 5;
    case ANULADA = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::SOLICITADA => 'Solicitada',
            self::ABIERTA => 'Abierta',
            self::BLOQUEADA => 'Bloqueada',
            self::PENDIENTE_DE_PAGO => 'Pendiente de pago',
            self::CERRADA => 'Cerrada',
            self::ANULADA => 'Anulada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SOLICITADA => 'info',
            self::ABIERTA => 'success',
            self::BLOQUEADA => 'danger',
            self::PENDIENTE_DE_PAGO => 'warning',
            self::CERRADA => 'gray',
            self::ANULADA => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SOLICITADA => 'heroicon-o-document-text',
            self::ABIERTA => 'heroicon-o-folder-open',
            self::BLOQUEADA => 'heroicon-o-lock-closed',
            self::PENDIENTE_DE_PAGO => 'heroicon-o-clock',
            self::CERRADA => 'heroicon-o-folder',
            self::ANULADA => 'heroicon-o-x-circle',
        };
    }
}
