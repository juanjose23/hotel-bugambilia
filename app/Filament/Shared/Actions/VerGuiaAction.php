<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions;

use Filament\Actions\Action;

final class VerGuiaAction
{
    public static function make(string $id): Action
    {
        return Action::make('guia_operativa')->label('Guía de este paso')->color('gray')->icon('heroicon-o-question-mark-circle')
            ->extraAttributes(['data-hotel-guide-dialog' => $id])
            ->alpineClickHandler("\$dispatch('hotel:guia', { id: \$el.dataset.hotelGuideDialog })");
    }
}
