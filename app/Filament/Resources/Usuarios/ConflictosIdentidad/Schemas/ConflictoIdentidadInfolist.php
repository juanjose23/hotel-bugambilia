<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\ConflictosIdentidad\Schemas;

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConflictoIdentidadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Conflicto')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('tipo_conflicto')
                                    ->label('Tipo de Conflicto')
                                    ->badge()
                                    ->color(fn (TipoConflictoIdentidad $state): string => $state->getColor())
                                    ->formatStateUsing(fn (TipoConflictoIdentidad $state): string => $state->getLabel()),
                                TextEntry::make('estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (EstadoConflictoIdentidad $state): string => $state->getColor())
                                    ->formatStateUsing(fn (EstadoConflictoIdentidad $state): string => $state->getLabel()),
                                TextEntry::make('creadoPor.name')
                                    ->label('Reportado por')
                                    ->placeholder('—'),
                                TextEntry::make('created_at')
                                    ->label('Fecha de Detección')
                                    ->dateTime(),
                            ]),
                    ]),

                Section::make('Persona Existente')
                    ->schema([
                        TextEntry::make('persona.nombre_completo')
                            ->label('Nombre')
                            ->placeholder('—'),
                        TextEntry::make('persona.telefono')
                            ->label('Teléfono')
                            ->placeholder('—'),
                        TextEntry::make('persona.direccion')
                            ->label('Dirección')
                            ->placeholder('—'),
                    ])
                    ->visible(fn ($record): bool => $record->persona !== null),

                Section::make('Datos Proporcionados')
                    ->schema([
                        KeyValueEntry::make('datos_providos')
                            ->label('Datos del Registro'),
                    ]),

                Section::make('Datos Existentes')
                    ->schema([
                        KeyValueEntry::make('datos_existentes')
                            ->label('Datos en el Sistema'),
                    ]),

                Section::make('Resolución')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('resueltoPor.name')
                                    ->label('Resuelto por')
                                    ->placeholder('—'),
                                TextEntry::make('resuelto_en')
                                    ->label('Fecha de Resolución')
                                    ->dateTime()
                                    ->placeholder('—'),
                            ]),
                        TextEntry::make('notas')
                            ->label('Notas')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record): bool => $record->estado !== EstadoConflictoIdentidad::Pendiente),
            ]);
    }
}
