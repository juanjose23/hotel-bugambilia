<?php

declare(strict_types=1);

namespace App\Filament\Resources\Servicios\Servicios\Tables;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroCategoria;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

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
                            return 'heroicon-o-check-badge';
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
                            default => 'heroicon-o-check-badge',
                        };
                    }),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

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

                FechaStandardColumn::make('deleted_at', 'Eliminado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FiltroEstado::make(EstadoGeneral::class),
                FiltroCategoria::make(CatalogoTipo::CATEGORIA_SERVICIO),
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
