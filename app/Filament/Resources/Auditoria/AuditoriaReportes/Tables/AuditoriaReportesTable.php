<?php

namespace App\Filament\Resources\Auditoria\AuditoriaReportes\Tables;

use App\BusinessLogic\Shared\Reportes\ReporteDispatcher;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditoriaReportesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('usuario.email')->label('Fecha'),
                TextColumn::make('tipo_reporte')->label('Codigo Reporte'),
                TextColumn::make('created_at')->label('Fecha'),
                TextColumn::make('parametros')
                    ->label('Parámetros')
                    ->state('...')
                    ->tooltip(function ($record): string {
                        return json_encode(
                            $record->parametros,
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ) ?: '';
                    })
                    ->copyable()
                    ->copyableState(fn ($record) => json_encode(
                        $record->parametros,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    )),
            ])
            ->filters([
                //
                SelectFilter::make('tipo_reporte')
                    ->label('Tipo de reporte')
                    ->searchable()
                    ->preload()
                    ->options(ReporteDispatcher::opcionesFiltro()),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
