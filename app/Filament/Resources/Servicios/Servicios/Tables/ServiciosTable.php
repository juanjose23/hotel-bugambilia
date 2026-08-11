<?php

namespace App\Filament\Resources\Servicios\Servicios\Tables;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
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

class ServiciosTable
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
                    ->icon(function ($record): string {
                        $st = $record->icono;
                        if (! $st) {
                            return 'heroicon-o-sparkles';
                        }
                        if (str_starts_with($st, 'heroicon-')) {
                            return $st;
                        }

                        return match ($st) {
                            'wifi' => 'heroicon-o-wifi',
                            'coffee' => 'heroicon-o-cup-soda',
                            'utensils', 'restaurant' => 'heroicon-o-building-storefront',
                            'bar' => 'heroicon-o-cake',
                            'pool', 'swimming' => 'heroicon-o-lifebuoy',
                            'sparkles' => 'heroicon-o-sparkles',
                            'car', 'parking' => 'heroicon-o-truck',
                            'gym' => 'heroicon-o-trophy',
                            'laundry', 'shirt' => 'heroicon-o-scissors',
                            'concierge', 'bell' => 'heroicon-o-bell',
                            'ac', 'wind' => 'heroicon-o-sun',
                            'tv' => 'heroicon-o-computer-desktop',
                            'bath' => 'heroicon-o-home-modern',
                            'lock' => 'heroicon-o-lock-closed',
                            'key' => 'heroicon-o-key',
                            'sun' => 'heroicon-o-sun',
                            'flame' => 'heroicon-o-fire',
                            'gift' => 'heroicon-o-gift',
                            'phone' => 'heroicon-o-phone',
                            'bed' => 'heroicon-o-home',
                            'calendar' => 'heroicon-o-calendar',
                            'card' => 'heroicon-o-credit-card',
                            'scissors' => 'heroicon-o-scissors',
                            'plane' => 'heroicon-o-paper-airplane',
                            'briefcase' => 'heroicon-o-briefcase',
                            'map' => 'heroicon-o-map',
                            default => 'heroicon-o-sparkles',
                        };
                    }),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state): ?string => is_string($color = EstadoGeneral::colorFor($state)) ? $color : null)
                    ->formatStateUsing(fn ($state): string => EstadoGeneral::labelFor($state))
                    ->sortable(),

                IconColumn::make('web')
                    ->label('Web')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FiltroEstado::make(EstadoGeneral::class),

                SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship(
                        name: 'categoria',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'catalogoTipo',
                            fn (Builder $q) => $q->where('codigo', CatalogoTipo::CATEGORIA_SERVICIO->value)
                        )
                    )
                    ->searchable()
                    ->preload(),

                FiltroEliminados::make(),
                TernaryFilter::make('web')
                    ->label('Mostrar en Web'),
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
