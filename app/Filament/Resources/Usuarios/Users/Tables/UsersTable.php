<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Users\Tables;

use App\Filament\Shared\Columns\FechaStandardColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['persona.cliente', 'persona.colaborador']))
            ->columns([
                TextColumn::make('persona.colaborador.codigo')
                    ->label('Código Colab.')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),

                TextColumn::make('persona.cliente.id')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record): string => $record->persona?->cliente ? 'Sí' : 'No')
                    ->badge()
                    ->color(fn ($state): string => $state === 'Sí' ? 'success' : 'gray'),

                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->sortable(),

                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('administradores')
                    ->query(fn (Builder $query): Builder => $query->where('is_admin', true)),
                Filter::make('clientes')
                    ->query(fn (Builder $query): Builder => $query->where('is_admin', false)->whereHas('persona.cliente')),
                Filter::make('sin_cliente')
                    ->query(fn (Builder $query): Builder => $query->where('is_admin', false)->whereDoesntHave('persona.cliente')),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
