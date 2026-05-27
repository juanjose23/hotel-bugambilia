<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CalendarioMantenimientos extends Page
{
    protected string $view = 'filament.pages.activos.calendario-mantenimientos';

    protected static ?string $slug = 'calendario-mantenimientos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Calendario de Mantenimiento';

    protected static ?string $title = 'Calendario de Órdenes de Mantenimiento';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = false;
}
