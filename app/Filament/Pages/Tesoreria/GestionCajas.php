<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Interactors\Tesoreria\GestionarCaja;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Queries\Tesoreria\OperacionCajaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use UnitEnum;

final class GestionCajas extends Page implements HasTable
{
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Caja y cierres';

    protected static ?string $navigationLabel = 'Gestión de cajas';

    protected static ?string $title = 'Gestión de cajas';

    protected static ?string $slug = 'tesoreria/cajas';

    protected static ?int $navigationSort = 21;

    protected string $view = 'filament.pages.tesoreria.gestion-cajas';

    public static function canAccess(): bool
    {
        return config('tesoreria.habilitado') && (auth()->user()?->can('ViewAny:Caja') ?? false);
    }

    public function table(Table $table): Table
    {
        return $table->query(app(OperacionCajaQuery::class)->listado())->columns([
            TextColumn::make('codigo')->label('Código')->searchable(), TextColumn::make('nombre')->searchable(), TextColumn::make('ubicacion')->label('Ubicación'),
            TextColumn::make('monedas.codigo')->label('Monedas')->badge(), TextColumn::make('sesionActiva.usuario.name')->label('Cajero')->placeholder('Sin turno activo'),
            TextColumn::make('sesionActiva.estado')->label('Turno')->formatStateUsing(fn (string $state): string => match ($state) {
                'abierta' => 'Abierto','pendiente_revision' => 'Diferencia por revisar',default => $state
            })->badge()->placeholder('Disponible'),
        ])->headerActions([
            Action::make('crear')->label('Crear caja')->icon('heroicon-o-plus')->visible(fn (): bool => auth()->user()?->can('Create:Caja') ?? false)->schema([
                TextInput::make('codigo')->label('Código')->maxLength(30)->required(), TextInput::make('nombre')->maxLength(100)->required(), TextInput::make('ubicacion')->label('Ubicación')->maxLength(150)->required(),
                Select::make('monedas')->label('Monedas del fondo')->multiple()->options(fn (): array => app(OperacionCajaQuery::class)->monedas())->required()->minItems(1),
            ])->action(function (array $data): void {
                /** @var array{codigo:string,nombre:string,ubicacion:string,monedas:list<int|string>} $data */
                $this->operar(fn () => app(GestionarCaja::class)->crear($this->actorId(), $data['codigo'], $data['nombre'], $data['ubicacion'], array_map(fn (int|string $id): int => (int) $id, $data['monedas'])), 'Caja creada');
            }),
            Action::make('miTurno')->label('Ir a mi turno')->visible(fn (): bool => auth()->user()?->can('Operar:Caja') ?? false)->url(MiTurnoCaja::getUrl()),
        ])->recordActions([
            Action::make('revisar')->label('Revisar arqueo')->modalWidth('3xl')->visible(fn (Caja $record): bool => $record->sesionActiva?->estado === 'pendiente_revision' && (auth()->user()?->can('CerrarDiferencia:Caja') ?? false))
                ->modalContent(fn (?Caja $record): View => view('filament.pages.tesoreria.resumen-cierre', ['sesion' => $record?->sesionActiva, 'saldos' => $record !== null && $record->sesionActiva !== null ? app(OperacionCajaQuery::class)->resumen($record->sesionActiva) : []]))
                ->modalSubmitActionLabel('Aprobar cierre y conservar diferencia')->action(function (Caja $record): void {
                    $this->operar(fn () => app(GestionarCaja::class)->aprobarCierre($this->actorId(), $record->sesionActiva !== null ? $record->sesionActiva->id : 0), 'Cierre aprobado; la diferencia permanece registrada');
                }),
        ]);
    }
}
