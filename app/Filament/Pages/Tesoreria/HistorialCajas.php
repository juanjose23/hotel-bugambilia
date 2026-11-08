<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\Cajas\CajasCluster;
use App\Repository\Models\Tesoreria\SesionCaja;
use App\Repository\Queries\Tesoreria\OperacionCajaQuery;
use BackedEnum;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use UnitEnum;

final class HistorialCajas extends Page implements HasTable
{
    protected static ?string $cluster = CajasCluster::class;

    use AccionesCaja, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Control y cierres';

    protected static ?string $navigationLabel = 'Arqueos y cierres';

    protected static ?string $title = 'Arqueos y cierres';

    protected static ?string $slug = 'tesoreria/cierres';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.pages.tesoreria.historial';

    public static function canAccess(): bool
    {
        return config('tesoreria.habilitado') && ((auth()->user()?->can('Auditar:Caja') ?? false) || (auth()->user()?->can('Operar:Caja') ?? false));
    }

    public function table(Table $table): Table
    {
        return $table->query(app(OperacionCajaQuery::class)->historial($this->actorId(), auth()->user()?->can('Auditar:Caja') ?? false))->columns([
            TextColumn::make('caja.nombre')->label('Caja')->searchable()->weight('bold'),
            TextColumn::make('usuario.name')->label('Responsable')->searchable(),
            TextColumn::make('estado')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                'abierta' => 'Abierta',
                'pendiente_revision' => 'Diferencia por revisar',
                'cerrada' => 'Cerrada',
                default => $state
            }),
            TextColumn::make('abierta_at')->label('Apertura')->dateTime('d/m/Y H:i'),
            TextColumn::make('cerrada_at')->label('Cierre')->dateTime('d/m/Y H:i')->placeholder('En curso'),
            TextColumn::make('duracion')
                ->label('Duración')
                ->state(fn (SesionCaja $record): string => $record->cerrada_at !== null
                    ? $record->abierta_at->diffForHumans($record->cerrada_at, CarbonInterface::DIFF_ABSOLUTE)
                    : 'Activo ('.$record->abierta_at->diffForHumans().')')
                ->badge()
                ->color(fn (SesionCaja $record): string => $record->cerrada_at !== null ? 'gray' : 'success'),
            TextColumn::make('movimientos_count')
                ->counts('movimientos')
                ->label('Movimientos')
                ->badge()
                ->color('info'),
        ])->recordActions([
            Action::make('ver')
                ->label('Ver arqueo y movimientos')
                ->icon('heroicon-o-eye')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->modalWidth('4xl')
                ->modalContent(fn (?SesionCaja $record): View => view('filament.pages.tesoreria.resumen-cierre', [
                    'sesion' => $record,
                    'saldos' => $record !== null ? app(OperacionCajaQuery::class)->resumen($record) : [],
                ])),
        ]);
    }
}
