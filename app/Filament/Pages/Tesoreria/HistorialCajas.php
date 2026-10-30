<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Repository\Models\Tesoreria\SesionCaja;
use App\Repository\Queries\Tesoreria\OperacionCajaQuery;
use BackedEnum;
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
    protected static ?string $cluster = FacturacionConfigCluster::class;

    use AccionesCaja, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Caja y cierres';

    protected static ?string $navigationLabel = 'Arqueos y cierres';

    protected static ?string $title = 'Arqueos y cierres';

    protected static ?string $slug = 'tesoreria/cierres';

    protected static ?int $navigationSort = 22;

    protected string $view = 'filament.pages.tesoreria.historial';

    public static function canAccess(): bool
    {
        return config('tesoreria.habilitado') && ((auth()->user()?->can('Auditar:Caja') ?? false) || (auth()->user()?->can('Operar:Caja') ?? false));
    }

    public function table(Table $table): Table
    {
        return $table->query(app(OperacionCajaQuery::class)->historial($this->actorId(), auth()->user()?->can('Auditar:Caja') ?? false))->columns([
            TextColumn::make('caja.nombre')->label('Caja'), TextColumn::make('usuario.name')->label('Responsable'), TextColumn::make('estado')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                'abierta' => 'Abierta','pendiente_revision' => 'Diferencia por revisar','cerrada' => 'Cerrada',default => $state
            }), TextColumn::make('abierta_at')->label('Apertura')->dateTime('d/m/Y H:i'), TextColumn::make('cerrada_at')->label('Cierre')->dateTime('d/m/Y H:i')->placeholder('Pendiente'),
        ])->recordActions([Action::make('ver')->label('Ver arqueo')->modalSubmitAction(false)->modalCancelActionLabel('Cerrar')->modalWidth('3xl')->modalContent(fn (?SesionCaja $record): View => view('filament.pages.tesoreria.resumen-cierre', ['sesion' => $record, 'saldos' => $record !== null ? app(OperacionCajaQuery::class)->resumen($record) : []]))]);
    }
}
