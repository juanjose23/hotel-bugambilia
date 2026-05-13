<?php

namespace App\Filament\Resources\Auditoria\AuditoriaReporte\Schemas;

use App\Models\Audits\AuditoriaReporte;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditoriaReporteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Detalles del reporte')
                    ->columns()
                    ->schema([
                        TextEntry::make('tipo_reporte')
                            ->label('Tipo de reporte')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-s-document-text'),

                        TextEntry::make('usuario.name')
                            ->label('Generado por')
                            ->icon('heroicon-s-user')
                            ->formatStateUsing(fn ($state, AuditoriaReporte $record) => ($u = $record->usuario)
                                ? ($u->name ?? $u->email)
                                : 'Sistema'
                            ),

                        TextEntry::make('conteo_descargas')
                            ->label('Descargas')
                            ->icon('heroicon-s-arrow-down-tray')
                            ->color('info'),

                        TextEntry::make('ultima_descarga_en')
                            ->label('Última descarga')
                            ->dateTime()
                            ->icon('heroicon-s-clock')
                            ->color('secondary'),

                        TextEntry::make('created_at')
                            ->label('Generado el')
                            ->dateTime()
                            ->icon('heroicon-s-calendar')
                            ->color('secondary')
                            ->columnSpanFull(),
                    ]),

                Section::make('Parámetros aplicados')
                    ->columns()
                    ->schema([
                        RepeatableEntry::make('parametros_list')
                            ->label('Filtros')
                            ->columnSpanFull()
                            ->hidden(fn ($record) => empty($record->parametros))
                            ->state(function ($record) {
                                $params = $record->parametros ?? [];
                                $result = [];
                                foreach ($params as $key => $value) {
                                    if ($value !== null && $value !== '' && $value !== []) {
                                        $result[] = [
                                            'parametro' => $key,
                                            'valor' => is_array($value) ? json_encode($value) : $value,
                                        ];
                                    }
                                }
                                return $result;
                            })
                            ->schema([
                                TextEntry::make('parametro')
                                    ->label('Parámetro')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('valor')
                                    ->label('Valor'),
                            ]),

                        TextEntry::make('ruta_archivo')
                            ->label('Archivo generado')
                            ->icon('heroicon-s-document')
                            ->color('gray')
                            ->columnSpanFull()
                            ->hidden(fn ($state) => blank($state)),
                    ]),
            ]);
    }
}
