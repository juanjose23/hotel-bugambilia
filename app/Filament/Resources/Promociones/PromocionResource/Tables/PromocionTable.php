<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\PromocionResource\Tables;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroEliminados;
use App\Filament\Shared\Filters\FiltroEstado;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromocionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagenes.url')
                    ->label('Imagen')
                    ->circular()
                    ->placeholder('-')
                    ->limit(1),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('tipo.nombre')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date()
                    ->sortable(),

                TextColumn::make('descuento_porcentaje')
                    ->label('% Desc.')
                    ->sortable()
                    ->placeholder('-')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}%" : '-'),

                EstadoBadgeColumn::make(EstadoGeneral::class)
                    ->sortable(),

                IconColumn::make('web')
                    ->label('Web')
                    ->boolean()
                    ->sortable(),

                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),

                FechaStandardColumn::make('updated_at', 'Actualizado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FiltroEstado::make(EstadoGeneral::class),

                SelectFilter::make('tipo_promocion_id')
                    ->label('Tipo')
                    ->relationship(
                        name: 'tipo',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'catalogoTipo',
                            fn (Builder $q) => $q->where('codigo', CatalogoTipo::TIPO_PROMOCION->value)
                        )
                    )
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('web')
                    ->label('Mostrar en Web'),

                FiltroEliminados::make(),
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
            ]);
    }
}
