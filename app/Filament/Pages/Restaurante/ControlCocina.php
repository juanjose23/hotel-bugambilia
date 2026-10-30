<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\Repository\Queries\Restaurante\Cocina\ControlOperacionCocinaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use UnitEnum;

final class ControlCocina extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante & Cocina';

    protected static ?string $navigationLabel = 'Control de cocina';

    protected static ?string $title = 'Control de cocina';

    protected static ?string $slug = 'restaurante/control-cocina';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.restaurante.control-cocina';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->can('Page:ControlCocina') || $user->can('Page:CocinaPedidos'));
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return ['resumen' => app(ControlOperacionCocinaQuery::class)->resumen()];
    }

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [Action::make('comandas')->label('Abrir comandas')->icon('heroicon-o-fire')->url(CocinaPedidos::getUrl())->visible(fn (): bool => CocinaPedidos::canAccess())];
    }
}
