<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos;

use App\Repository\Queries\Activos\Mantenimiento\ObtenerMantenimientosCalendarioQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CalendarioMantenimientos extends Page
{
    use HasPageShield;

    protected string $view = 'filament.pages.activos.calendario-mantenimientos';

    protected static ?string $slug = 'calendario-mantenimientos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Calendario de Mantenimiento';

    protected static ?string $title = 'Calendario de Órdenes de Mantenimiento';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = false;

    public int $month;

    public int $year;

    public function mount(): void
    {
        $this->month = (int) now()->month;
        $this->year = (int) now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::now()->setDate($this->year, $this->month, 1)->startOfDay()->subMonth();
        $this->month = (int) $date->month;
        $this->year = (int) $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::now()->setDate($this->year, $this->month, 1)->startOfDay()->addMonth();
        $this->month = (int) $date->month;
        $this->year = (int) $date->year;
    }

    public function goToToday(): void
    {
        $this->month = (int) now()->month;
        $this->year = (int) now()->year;
    }

    protected function getViewData(): array
    {
        $firstDayOfMonth = Carbon::now()->setDate($this->year, $this->month, 1)->startOfDay();
        $daysInMonth = $firstDayOfMonth->daysInMonth;

        $firstDayOfWeek = $firstDayOfMonth->dayOfWeekIso;

        $days = [];

        for ($i = 1; $i < $firstDayOfWeek; $i++) {
            $days[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $days[] = $day;
        }

        $mantenimientos = ObtenerMantenimientosCalendarioQuery::make()
            ->ejecutar($this->month, $this->year)
            ->groupBy(fn ($m) => $m->fecha_programada->day);

        $firstDayOfMonth->locale('es');
        $nombreMes = ucfirst($firstDayOfMonth->translatedFormat('F'));

        return [
            'days' => $days,
            'mantenimientos' => $mantenimientos,
            'nombreMes' => $nombreMes,
        ];
    }
}
