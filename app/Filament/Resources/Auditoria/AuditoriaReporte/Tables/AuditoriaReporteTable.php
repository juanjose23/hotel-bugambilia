<?php

namespace App\Filament\Resources\Auditoria\AuditoriaReporte\Tables;

use App\Models\Audits\AuditoriaReporte;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditoriaReporteTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo_reporte')
                    ->label('Reporte')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('usuario.name')
                    ->label('Generado por')
                    ->formatStateUsing(function ($state, $record) {
                        $usuario = $record->usuario;
                        if (! $usuario) {
                            return 'Sistema';
                        }

                        return $usuario->name ?: $usuario->email;
                    })
                    ->searchable(),

                TextColumn::make('conteo_descargas')
                    ->label('Descargas')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('ultima_descarga_en')
                    ->label('Última descarga')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Generado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo_reporte')
                    ->label('Tipo de reporte')
                    ->options(
                        AuditoriaReporte::query()
                            ->select('tipo_reporte')
                            ->distinct()
                            ->pluck('tipo_reporte', 'tipo_reporte')
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
