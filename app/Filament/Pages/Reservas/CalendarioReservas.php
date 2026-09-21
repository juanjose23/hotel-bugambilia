<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Filament\Shared\Forms\CategoriaSelect;
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
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @property Schema $form
 */
final class CalendarioReservas extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Recepción & Reservas';

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
                                    ->label('Tipo de Reserva')
                                    ->options(array_merge(
                                        ['todos' => 'Todos los recursos'],
                                        TipoReserva::options(),
                                    ))
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn ($state) => $this->updatedFiltroTipoState((string) $state)),

                                CategoriaSelect::make(
                                    tipo: CatalogoTipo::CATEGORIA_HABITACION,
                                    column: 'categoriaFiltro',
                                    label: 'Categoría de Habitación',
                                )
                                    ->placeholder('Todas las Categorías')
                                    ->nullable()
                                    ->live(),

                                Select::make('estadoFiltro')
                                    ->label('Estado de Reserva')
                                    ->options(array_merge(
                                        [
                                            0 => 'Activas (Excl. Canceladas)',
                                            -1 => 'Todas (Incl. Canceladas)',
                                        ],
                                        EstadoReserva::options(),
                                    ))
                                    ->native(false)
                                    ->live(),

                                TextInput::make('buscar')
                                    ->label('Búsqueda Rápida')
                                    ->placeholder('Nombre cliente, código RES...')
                                    ->live(debounce: 300),
                            ]),
                    ]),
            ])
            ->statePath('filterData');
    }

    private function updatedFiltroTipoState(string $tipo): void
    {
        $this->tabActiva = $tipo;
    }

    public function cambiarTab(string $tab): void
    {
        $this->tabActiva = $tab;
        $this->filterData['filtroTipo'] = $tab;
        $this->form->fill($this->filterData);
    }

    public function updatedMonth(): void
    {
        $this->month = (int) $this->month;
    }

    public function updatedYear(): void
    {
        $this->year = (int) $this->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay()->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay()->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function goToToday(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    protected function getViewData(): array
    {
        return [
            'calendarioData' => $this->buildCalendarioData(),
        ];
    }

    /**
     * @return array{
     *     days: array<int, int|null>,
     *     nombreMes: string,
     *     year: int,
     *     month: int,
     *     categorias_habitacion: array<int, string>,
     *     reservasPorDia: Collection<int, Collection<int, array{id: int, codigo: string, cliente: string, telefono: string, tipo: string, estado: string, estado_enum: int, estado_color: string, fecha_check_in: string, fecha_check_out: string, habitacion_id: int|null, espacio_id: int|null, recurso_nombre: string, total: float, day_check_in: int, es_llegada: bool, es_salida: bool}>>,
     *     disponibilidadHabitaciones: array{total_habitaciones: int, dias_agotados: array<int, string>, ocupacion_por_dia: array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}>}|null,
     *     totalReservas: int,
     *     totalMonto: float
     * }
     */
    private function buildCalendarioData(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->filterData ?? [];

        $tipo = isset($data['filtroTipo']) && is_string($data['filtroTipo']) ? $data['filtroTipo'] : $this->tabActiva;
        $estado = isset($data['estadoFiltro']) && is_numeric($data['estadoFiltro']) ? (int) $data['estadoFiltro'] : 0;
        $categoria = isset($data['categoriaFiltro']) && is_string($data['categoriaFiltro']) ? $data['categoriaFiltro'] : '';
        $buscar = isset($data['buscar']) && is_string($data['buscar']) ? $data['buscar'] : '';

        return $this->query->ejecutar(
            $this->month,
            $this->year,
            $tipo,
            $estado > 0 ? $estado : null,
            $categoria !== '' ? $categoria : null,
            $buscar !== '' ? $buscar : null,
        );
    }
}
