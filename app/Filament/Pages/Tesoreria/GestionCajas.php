<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\Cajas\CajasCluster;
use App\Interactors\Tesoreria\GestionarCaja;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Queries\Tesoreria\OperacionCajaQuery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class GestionCajas extends Page implements HasTable
{
    protected static ?string $cluster = CajasCluster::class;

    use AccionesCaja, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Control y cierres';

    protected static ?string $navigationLabel = 'Gestión de cajas';

    protected static ?string $title = 'Gestión de cajas';

    protected static ?string $slug = 'tesoreria/cajas';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.tesoreria.gestion-cajas';

    public static function canAccess(): bool
    {
        return config('tesoreria.habilitado') && (auth()->user()?->can('ViewAny:Caja') ?? false);
    }

    public function table(Table $table): Table
    {
        return $table->query(app(OperacionCajaQuery::class)->listado())->columns([
            TextColumn::make('codigo')->label('Código')->searchable()->weight('bold'),
            TextColumn::make('nombre')->label('Caja')->searchable(),
            TextColumn::make('tipoCaja.nombre')
                ->label('Tipo de caja')
                ->badge()
                ->color('info')
                ->placeholder('General'),
            TextColumn::make('ubicacion_display')
                ->label('Ubicación')
                ->state(fn (Caja $record): string => $record->ubicacionRelacionada !== null ? $record->ubicacionRelacionada->nombre : ($record->ubicacion ?? '—'))
                ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('ubicacion', 'ilike', "%{$search}%")->orWhereRelation('ubicacionRelacionada', 'nombre', 'ilike', "%{$search}%")),
            TextColumn::make('series_fiscales_count')
                ->counts('seriesFiscales')
                ->label('Series DGI')
                ->badge()
                ->color('gray'),
            TextColumn::make('monedas.codigo')->label('Monedas')->badge(),
            TextColumn::make('sesionActiva.usuario.name')
                ->label('Cajero')
                ->description(fn (Caja $record): ?string => $record->sesionActiva !== null ? 'Abierto '.$record->sesionActiva->abierta_at->diffForHumans() : null)
                ->placeholder('Sin turno activo'),
            TextColumn::make('sesionActiva.estado')->label('Turno')->formatStateUsing(fn (string $state): string => match ($state) {
                'abierta' => 'Abierto',
                'pendiente_revision' => 'Diferencia por revisar',
                default => $state
            })->badge()->placeholder('Disponible'),
        ])->headerActions([
            Action::make('crear')->label('Crear caja')->icon('heroicon-o-plus')->visible(fn (): bool => auth()->user()?->can('Create:Caja') ?? false)->schema([
                TextInput::make('codigo')->label('Código')->maxLength(30)->placeholder('Ej. CJ-LOBBY-01')->required(),
                TextInput::make('nombre')->maxLength(100)->placeholder('Ej. Caja Recepción Lobby')->required(),
                Select::make('tipo_caja_id')
                    ->label('Tipo de caja')
                    ->options(fn (): array => Catalogo::query()
                        ->whereRelation('catalogoTipo', 'codigo', 'TIPO_CAJA')
                        ->where('estado', 1)
                        ->orderBy('orden')
                        ->pluck('nombre', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
                Select::make('ubicacion_id')
                    ->label('Ubicación física')
                    ->options(fn (): array => Ubicacion::query()
                        ->where('estado', 1)
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if ($state) {
                            $ubi = Ubicacion::query()->find($state);
                            if ($ubi) {
                                $set('ubicacion', $ubi->nombre);
                            }
                        }
                    }),
                TextInput::make('ubicacion')->label('Referencia / Detalle de ubicación')->maxLength(150)->required(),
                Select::make('monedas')->label('Monedas del fondo')->multiple()->options(fn (): array => app(OperacionCajaQuery::class)->monedas())->required()->minItems(1),
            ])->action(function (array $data): void {
                /** @var array{codigo:string,nombre:string,ubicacion:string,monedas:list<int|string>,ubicacion_id?:int|string|null,tipo_caja_id?:int|string|null} $data */
                $ubicacionId = ! empty($data['ubicacion_id']) ? (int) $data['ubicacion_id'] : null;
                $tipoCajaId = ! empty($data['tipo_caja_id']) ? (int) $data['tipo_caja_id'] : null;
                $this->operar(fn () => app(GestionarCaja::class)->crear(
                    $this->actorId(),
                    $data['codigo'],
                    $data['nombre'],
                    $data['ubicacion'],
                    array_map(fn (int|string $id): int => (int) $id, $data['monedas']),
                    $ubicacionId,
                    $tipoCajaId
                ), 'Caja creada');
            }),
            Action::make('miTurno')->label('Ir a mi turno')->visible(fn (): bool => auth()->user()?->can('Operar:Caja') ?? false)->url(MiTurnoCaja::getUrl()),
        ])->recordActions([
            Action::make('verTurno')
                ->label('Ver turno')
                ->icon('heroicon-o-eye')
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->visible(fn (Caja $record): bool => $record->sesionActiva !== null && $record->sesionActiva->estado === 'abierta')
                ->modalContent(fn (Caja $record): View => view('filament.pages.tesoreria.resumen-cierre', [
                    'sesion' => $record->sesionActiva,
                    'saldos' => $record->sesionActiva !== null ? app(OperacionCajaQuery::class)->resumen($record->sesionActiva) : [],
                ])),
            Action::make('revisar')->label('Revisar arqueo')->modalWidth('4xl')->visible(fn (Caja $record): bool => $record->sesionActiva?->estado === 'pendiente_revision' && (auth()->user()?->can('CerrarDiferencia:Caja') ?? false))
                ->modalContent(fn (?Caja $record): View => view('filament.pages.tesoreria.resumen-cierre', ['sesion' => $record?->sesionActiva, 'saldos' => $record !== null && $record->sesionActiva !== null ? app(OperacionCajaQuery::class)->resumen($record->sesionActiva) : []]))
                ->modalSubmitActionLabel('Aprobar cierre y conservar diferencia')->action(function (Caja $record): void {
                    $this->operar(fn () => app(GestionarCaja::class)->aprobarCierre($this->actorId(), $record->sesionActiva !== null ? $record->sesionActiva->id : 0), 'Cierre aprobado; la diferencia permanece registrada');
                }),
        ]);
    }
}
