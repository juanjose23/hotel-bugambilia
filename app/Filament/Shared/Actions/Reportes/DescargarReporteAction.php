<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Reportes;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Support\Icons\Heroicon;

class DescargarReporteAction
{
    /**
     * @param  array<int, Component|Action|ActionGroup>  $formSchema
     */
    public static function make(
        string $name,
        string $label,
        string $modalHeading,
        string $modalDescription,
        string $routeName,
        array $formSchema = [],
        bool $optionalParam = false
    ): Action {

        return Action::make($name)
            ->label($label)
            ->modalHeading($modalHeading)
            ->modalDescription($modalDescription)
            ->icon(Heroicon::DocumentArrowDown)
            ->color('primary')
            ->schema($formSchema)
            ->action(function (array $data) use ($routeName) {
                return redirect()->route($routeName, $data);
            });
    }
}
