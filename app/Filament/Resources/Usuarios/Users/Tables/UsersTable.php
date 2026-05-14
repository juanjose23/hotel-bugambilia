<?php

namespace App\Filament\Resources\Usuarios\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('persona.colaborador.codigo')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre de usuario')
                    ->searchable(),
                TextColumn::make('persona.primer_nombre')
                    ->label('Trabajador')
                    ->formatStateUsing(fn ($record): string => $record->persona
                        ? trim($record->persona->primer_nombre.' '.
                            ($record->persona->segundo_nombre ?? '').' '.
                            ($record->persona->personaNatural->primer_apellido ?? '').' '.
                            ($record->persona->personaNatural->segundo_apellido ?? ''))
                        : 'Sin trabajador asociado'
                    )
                    ->searchable(query: fn ($query, $search) => $query
                        ->whereHas('persona', fn ($q) => $q
                            ->where('primer_nombre', 'like', "%{$search}%")
                            ->orWhere('segundo_nombre', 'like', "%{$search}%")
                        )
                        ->orWhereHas('persona.personaNatural', fn ($q) => $q
                            ->where('primer_apellido', 'like', "%{$search}%")
                            ->orWhere('segundo_apellido', 'like', "%{$search}%")
                        )
                    ),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
