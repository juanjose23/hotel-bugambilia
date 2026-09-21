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
use App\Repository\Models\Servicios\Servicio;
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
use Illuminate\Database\Eloquent\Builder;

class ServiciosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['categoria', 'politicas', 'imagenes']))
            ->columns([
                ImageColumn::make('imagen_principal')
                    ->label('Imagen')
                    ->state(fn (Servicio $record): ?string => $record->imagenes->sortBy('orden')->first()?->url_completa)
                    ->circular()
                    ->placeholder('-'),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->icon(fn (Servicio $record): string => $record->icono_filament),

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
