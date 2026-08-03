<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Reportes\ObtenerReportesRestauranteQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

final class ReportesRestaurante extends Page implements HasTable
{
    use HasPageShield,InteractsWithTable;

    protected static UnitEnum|string|null $navigationGroup = 'Restaurante';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes del Restaurante';

    protected static ?string $slug = 'restaurante/reportes';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.resources.restaurante.reportes-restaurante';

    /** @var array<string, mixed> */
    public array $resumen = [];

    /** @var array<int, mixed> */
    public array $topPlatos = [];

    /** @var array<int, mixed> */
    public array $porCategoria = [];

    public int $totalPedidos = 0;

    public string $fechaInicio;

    public string $fechaFin;

    private ObtenerReportesRestauranteQuery $reportesQuery;

    public function boot(ObtenerReportesRestauranteQuery $reportesQuery): void
    {
        $this->reportesQuery = $reportesQuery;
    }

    public function mount(): void
    {
        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin = now()->toDateString();
        $this->cargarReportes();
    }

    public function cargarReportes(): void
    {
        $datos = $this->reportesQuery->ejecutar($this->fechaInicio, $this->fechaFin);

        $this->resumen = $datos['resumen'];
        $this->topPlatos = $datos['topPlatos'];
        $this->porCategoria = $datos['porCategoria'];
        $this->totalPedidos = $datos['totalPedidos'];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->reportesQuery->pedidosParaTabla($this->fechaInicio, $this->fechaFin)
            )
            ->columns([
                TextColumn::make('codigo')->label('Pedido')->searchable()->sortable(),
                TextColumn::make('mesa.nombre')->label('Mesa'),
                TextColumn::make('mesero.persona.nombre_completo')->label('Mesero'),
                TextColumn::make('estado')->label('Estado')->badge()
                    ->formatStateUsing(fn (mixed $state): string => EstadoPedido::resolveLabel($state))
                    ->color(fn (mixed $state): string => EstadoPedido::resolveColor($state)),
                TextColumn::make('total')->label('Total')->money('NIO')->sortable(),
                TextColumn::make('created_at')->label('Fecha')->dateTime()->sortable(),
            ])
            ->poll('30s');
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user !== null && $user->can('page_ReportesRestaurante');
    }
}
