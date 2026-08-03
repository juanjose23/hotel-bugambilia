<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\Repository\Queries\Reservas\ObtenerCalendarioReservasQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * @property Schema $form
 */
final class CalendarioReservas extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Reservaciones';

    protected static ?string $navigationLabel = 'Calendario de Reservas';

    protected static ?string $title = 'Calendario de Reservaciones';

    protected static ?string $slug = 'reservas/calendario';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.resources.reservas.calendario-reservas';

    public int $month;

    public int $year;

    public string $tabActiva = 'habitaciones'; // 'habitaciones', 'espacios', 'todos'

    /** @var array<string, mixed>|null */
    public ?array $filterData = [];

    /** @var array<string, mixed> */
    public array $calendarioData = [];

    private ObtenerCalendarioReservasQuery $query;

    public function boot(ObtenerCalendarioReservasQuery $query): void
    {
        $this->query = $query;
    }

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;

        $this->filterData = [
            'filtroTipo' => 'habitaciones',
            'estadoFiltro' => 0,
            'categoriaFiltro' => '',
            'buscar' => '',
        ];

        $this->form->fill($this->filterData);
        $this->cargarCalendario();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Filtros de Búsqueda de Reservaciones')
                    ->description('Filtre el calendario de reservaciones por tipo de recurso, categoría de habitación o estado.')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 4,
                        ])
                            ->schema([
                                Select::make('filtroTipo')
                                    ->label('Tipo de reserva')
                                    ->options([
                                        'habitaciones' => 'Habitaciones',
                                        'espacios' => 'Espacios / áreas comunes',
                                        'todos' => 'Todos los recursos',
                                    ])
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn ($state) => $this->updatedFiltroTipoState((string) $state)),

                                Select::make('categoriaFiltro')
                                    ->label('Categoría / Modelo')
                                    ->placeholder('Todas las Categorías')
                                    ->options(fn (): array => $this->getAvailableCategorias())
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->cargarCalendario()),

                                Select::make('estadoFiltro')
                                    ->label('Estado de Reserva')
                                    ->options([
                                        0 => 'Todos los Estados',
                                        1 => 'Pendiente',
                                        2 => 'Confirmada',
                                        3 => 'Checked In',
                                        4 => 'Checked Out',
                                        5 => 'Cancelada',
                                    ])
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->cargarCalendario()),

                                TextInput::make('buscar')
                                    ->label('Búsqueda Rápida')
                                    ->placeholder('Nombre cliente, código RES...')
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(fn () => $this->cargarCalendario()),
                            ]),
                    ]),
            ])
            ->statePath('filterData');
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableCategorias(): array
    {
        /** @var array<int, mixed> $cats */
        $cats = is_array($this->calendarioData['categorias_habitacion'] ?? null)
            ? $this->calendarioData['categorias_habitacion']
            : [];

        $assoc = [];
        foreach ($cats as $c) {
            if (is_string($c) && $c !== '') {
                $assoc[$c] = $c;
            }
        }

        return $assoc;
    }

    private function updatedFiltroTipoState(string $tipo): void
    {
        if ($tipo === 'habitaciones' || $tipo === 'espacios' || $tipo === 'todos') {
            $this->tabActiva = $tipo;
        }
        $this->cargarCalendario();
    }

    public function cambiarTab(string $tab): void
    {
        $this->tabActiva = $tab;
        $this->filterData['filtroTipo'] = $tab;
        $this->form->fill($this->filterData);
        $this->cargarCalendario();
    }

    public function previousMonth(): void
    {
        $date = Carbon::now()->setDate($this->year, $this->month, 1)->startOfDay()->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
        $this->cargarCalendario();
    }

    public function nextMonth(): void
    {
        $date = Carbon::now()->setDate($this->year, $this->month, 1)->startOfDay()->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
        $this->cargarCalendario();
    }

    public function goToToday(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
        $this->cargarCalendario();
    }

    public function cargarCalendario(): void
    {
        /** @var array<string, mixed> $data */
        $data = $this->filterData ?? [];

        $tipo = isset($data['filtroTipo']) && is_string($data['filtroTipo']) ? $data['filtroTipo'] : $this->tabActiva;
        $estado = isset($data['estadoFiltro']) && is_numeric($data['estadoFiltro']) ? (int) $data['estadoFiltro'] : 0;
        $categoria = isset($data['categoriaFiltro']) && is_string($data['categoriaFiltro']) ? $data['categoriaFiltro'] : '';
        $buscar = isset($data['buscar']) && is_string($data['buscar']) ? $data['buscar'] : '';

        $this->calendarioData = $this->query->ejecutar(
            $this->month,
            $this->year,
            $tipo,
            $estado > 0 ? $estado : null,
            $categoria !== '' ? $categoria : null,
            $buscar !== '' ? $buscar : null,
        );
    }
}
