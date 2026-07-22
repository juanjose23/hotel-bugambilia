<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

trait TieneAccionesImprimirExportar
{
    public static function makeImprimirAction(
        string $routeName,
        string $permission,
        string $label = 'Imprimir',
        ?callable $urlCallback = null,
        ?callable $visibleCallback = null
    ): Action {
        $name = 'imprimir_'.strtolower(str_replace([' ', '-'], '_', $label));

        return Action::make($name)
            ->label($label)
            ->icon(Heroicon::Printer)
            ->color('gray')
            ->url(fn ($record) => ($record !== null) ? ($urlCallback !== null ? call_user_func($urlCallback, $record) : route($routeName, $record)) : '')
            ->openUrlInNewTab()
            ->visible(function ($record) use ($permission, $visibleCallback) {
                if ($record === null) {
                    return false;
                }

                if ($visibleCallback !== null && ! call_user_func($visibleCallback, $record)) {
                    return false;
                }

                return auth()->user()?->can($permission) ?? false;
            });
    }
}
