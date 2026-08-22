<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource\Tables;

use App\Filament\Shared\Filters\FiltroEliminados;
use App\Repository\Models\Limpieza\Turno;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TurnoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['lider.persona.personaNatural', 'apoyo.persona.personaNatural', 'carritos']))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre del Turno')
                    ->sortable()
                    ->searchable()
                    ->icon(Heroicon::CheckBadge),

                TextColumn::make('lider')
                    ->label('Líder')
                    ->state(function (Turno $record): string {
                        $p = $record->lider?->persona;

                        return $p
                            ? trim($p->primer_nombre.' '.($p->personaNatural->primer_apellido ?? ''))
                            : 'Sin asignar';
                    })
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('lider.persona', function ($q) use ($search) {
                            $q->where('primer_nombre', 'like', "%{$search}%");
                        });
                    })
                    ->icon(Heroicon::User),

                TextColumn::make('apoyo')
                    ->label('Apoyo')
                    ->state(function (Turno $record): string {
                        $p = $record->apoyo?->persona;

                        return $p
                            ? trim($p->primer_nombre.' '.($p->personaNatural->primer_apellido ?? ''))
                            : 'Sin asignar';
                    })
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('apoyo.persona', function ($q) use ($search) {
                            $q->where('primer_nombre', 'like', "%{$search}%");
                        });
                    })
                    ->icon(Heroicon::UserGroup),

                TextColumn::make('carritos_names')
                    ->label('Carritos/Bodegas')
                    ->getStateUsing(function (Turno $record) {
                        return $record->carritos->pluck('nombre')->join(', ') ?: 'Sin asignar';
                    })
                    ->icon(Heroicon::ShoppingBag),

                TextColumn::make('hora_inicio')
                    ->label('Inicio')
                    ->time('H:i')
                    ->sortable()
                    ->icon(Heroicon::Clock),

                TextColumn::make('hora_fin')
                    ->label('Fin')
                    ->time('H:i')
                    ->sortable()
                    ->icon(Heroicon::Clock),

                IconColumn::make('es_lavanderia')
                    ->label('Lavandería')
                    ->boolean()
                    ->trueIcon('heroicon-o-sparkles')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('primary')
                    ->falseColor('gray'),

                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                TernaryFilter::make('es_lavanderia')
                    ->label('Tipo de Turno')
                    ->placeholder('Todos los turnos')
                    ->trueLabel('Solo Lavandería')
                    ->falseLabel('Solo Habitaciones / General'),
                FiltroEliminados::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ]);
    }
}
