<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\InventarioFisico\Tables;

use App\Enums\Inventario\EstadoInventarioFisico;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Inventario\InventarioFisico\ProcesarInventarioFisico;
use App\Repository\Models\Inventario\InventarioFisico;
use Exception;
use Filament\Actions\Action as TableAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Qalainau\UniverSheet\SpreadsheetColumn;

readonly class InventarioFisicoTable
{
    use InyectaDesdeContenedor;

    public function __construct(
        private ProcesarInventarioFisico $procesarInventarioFisico,
    ) {}

    public static function configure(Table $table): Table
    {
        return static::make()->doConfigure($table);
    }

    private function doConfigure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('fecha_toma')
                    ->label('Fecha Toma')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('creador.name')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),

                class_exists(SpreadsheetColumn::class)
                    ? SpreadsheetColumn::make('datos_hoja')
                        ->label('Vista Previa Hoja')
                        ->previewRows(4)
                        ->previewColumns(6)
                    : TextColumn::make('datos_hoja')
                        ->label('Lotes en Hoja')
                        ->formatStateUsing(function ($state): string {
                            if (! is_array($state)) {
                                return '0 lotes';
                            }
                            $cells = data_get($state, 'sheets.sheet-1.cellData');
                            $cellsArray = is_array($cells) ? $cells : [];

                            return count($cellsArray) > 1 ? (count($cellsArray) - 1).' lotes' : '0 lotes';
                        })
                        ->badge()
                        ->color('gray'),

                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FiltroEstado::make(EstadoInventarioFisico::class),
            ])
            ->recordActions([
                TableAction::make('procesar_conciliacion')
                    ->label('Conciliar')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Procesar Conciliación de Inventario')
                    ->modalDescription('Esta acción comparará la cantidad física registrada en la hoja de cálculo con el stock actual del sistema, generará los movimientos de ajuste (MOV_AJUSTE) en los lotes con discrepancia, y cerrará esta sesión como PROCESADO. Esta acción no se puede deshacer.')
                    ->action(function (InventarioFisico $record) {
                        try {
                            $this->procesarInventarioFisico->execute($record, (int) auth()->id());

                            Notification::make()
                                ->title('Conciliación Procesada')
                                ->body("La toma de inventario $record->codigo ha sido procesada de manera exitosa y los ajustes han sido aplicados.")
                                ->success()
                                ->send();
                        } catch (Exception $e) {

                            Notification::make()
                                ->title('Error al procesar conciliación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (InventarioFisico $record) => $record->estado === EstadoInventarioFisico::Borrador),

                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (InventarioFisico $record) => $record->estado === EstadoInventarioFisico::Borrador),
                DeleteAction::make()
                    ->visible(fn (InventarioFisico $record) => $record->estado === EstadoInventarioFisico::Borrador),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
