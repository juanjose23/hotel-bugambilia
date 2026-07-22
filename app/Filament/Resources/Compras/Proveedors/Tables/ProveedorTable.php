<?php

namespace App\Filament\Resources\Compras\Proveedors\Tables;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroEliminados;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProveedorTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('persona.nombre_completo')
                    ->label('Razón Social / Nombre')
                    ->searchable()
                    ->sortable()
                    ->state(fn ($record) => ($record->persona && $record->persona->personaJuridica)
                        ? $record->persona->personaJuridica->razon_social
                        : ($record->persona ? $record->persona->nombre_completo : '—')
                    ),

                TextColumn::make('tipoProveedor.nombre')
                    ->label('Tipo de Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contactoPrincipal.telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),

                EstadoBadgeColumn::make(EstadoGeneral::class)
                    ->searchable()
                    ->sortable(),

            ])
            ->filters([
                FiltroEliminados::make(),

                SelectFilter::make('tipo_proveedor_id')
                    ->label('Tipo de Proveedor')
                    ->relationship(
                        name: 'tipoProveedor',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'catalogoTipo',
                            fn (Builder $q) => $q->where('codigo', CatalogoTipo::TIPO_PROVEEDOR->value)
                        )
                    )
                    ->searchable()
                    ->preload(),

            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ])
            ->defaultSort('codigo');
    }
}
