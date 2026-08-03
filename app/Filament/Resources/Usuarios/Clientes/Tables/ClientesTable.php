<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Clientes\Tables;

use App\Repository\Models\Personas\Persona;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->searchable(query: fn (Builder $query, string $search): Builder => Persona::filtrarPorNombre($query, $search))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('primer_nombre', $direction === 'desc' ? 'desc' : 'asc')),
                TextColumn::make('personaNatural.numero_identificacion')
                    ->label('Identificación')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('cliente.tipoCliente.nombre')
                    ->label('Tipo')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ]);
    }
}
