<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDocumento\Tables;

use App\Filament\Shared\Columns\ColaboradorNombreColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroEliminados;
use App\Repository\Models\Colaboradores\ColaboradorDocumento;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ColaboradorDocumentoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitle(fn (ColaboradorDocumento $record): string => $record->tipo ?? 'Documento')
            ->columns([
                ColaboradorNombreColumn::make('colaborador.persona.nombre_completo'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('archivo')
                    ->label('Archivo')
                    ->limit(35)
                    ->url(fn (ColaboradorDocumento $record): string => asset('storage/'.$record->archivo))
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon(Heroicon::Document),
                FechaStandardColumn::make('created_at', 'Fecha de Carga')
                    ->sortable(),
            ])
            ->filters([
                FiltroEliminados::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Editar documento')
                    ->modalWidth('2xl'),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
