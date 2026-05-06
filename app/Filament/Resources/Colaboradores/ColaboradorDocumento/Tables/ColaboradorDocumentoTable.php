<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDocumento\Tables;

use App\Models\Colaboradores\ColaboradorDocumento;
use App\UseCases\Colaboradores\ObtenerNombreCompleto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ColaboradorDocumentoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitle(fn(ColaboradorDocumento $record): string => $record->tipo ?? 'Documento')
            ->columns([
                TextColumn::make('colaborador')
                    ->label('Colaborador')
                    ->formatStateUsing(
                        fn ($record) => app(ObtenerNombreCompleto::class)
                            ->obtenerNombreCompleto($record->colaborador)
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('archivo')
                    ->label('Archivo')
                    ->limit(35)
                    ->url(fn(ColaboradorDocumento $record): string => asset('storage/' . $record->archivo))
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-o-document'),
                TextColumn::make('created_at')
                    ->label('Fecha de Carga')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
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
