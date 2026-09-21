<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Users\Tables;

use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Repository\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['persona.cliente', 'persona.colaborador', 'roles']))
            ->columns([
                TextColumn::make('name')
                    ->label('Usuario')
                    ->description(fn (User $record): ?string => $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->weight('bold')
                    ->icon(Heroicon::UserCircle),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('primary')
                    ->separator(', ')
                    ->searchable()
                    ->placeholder('Sin rol asignado'),

                TextColumn::make('tipo_perfil')
                    ->label('Perfil Vinculado')
                    ->state(function (User $record): string {
                        if ($record->persona?->colaborador) {
                            return 'Colaborador: '.($record->persona->colaborador->codigo ?? 'Activo');
                        }

                        if ($record->persona?->cliente) {
                            return 'Cliente: '.($record->persona->nombre_completo ?? 'Registrado');
                        }

                        return 'Usuario General';
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        if ($record->persona?->colaborador) {
                            return 'info';
                        }
                        if ($record->persona?->cliente) {
                            return 'warning';
                        }

                        return 'gray';
                    })
                    ->icon(function (User $record): Heroicon {
                        if ($record->persona?->colaborador) {
                            return Heroicon::Briefcase;
                        }
                        if ($record->persona?->cliente) {
                            return Heroicon::UserGroup;
                        }

                        return Heroicon::User;
                    }),

                TextColumn::make('is_admin')
                    ->label('Acceso Panel')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Acceso Administrativo' : 'Acceso Público / Web')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->icon(fn (bool $state): Heroicon => $state ? Heroicon::ShieldCheck : Heroicon::GlobeAlt)
                    ->sortable(),

                FechaStandardColumn::make('created_at')
                    ->label('Registrado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Filtrar por Rol')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('is_admin')
                    ->label('Tipo de Acceso')
                    ->trueLabel('Solo Administradores')
                    ->falseLabel('Solo Usuarios Web / Clientes')
                    ->placeholder('Todos los accesos'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalWidth('2xl'),
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
