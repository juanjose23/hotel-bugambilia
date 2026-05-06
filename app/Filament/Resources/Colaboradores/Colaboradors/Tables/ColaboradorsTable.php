<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Tables;

use App\Enums\EstadoCatalogo;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ColaboradorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(components: [
                ImageColumn::make('colaborador.imagen.url')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('colaborador.codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('primer_nombre')
                    ->label('Nombres')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => trim($record->primer_nombre.' '.($record->segundo_nombre ?? ''))),

                TextColumn::make('personaNatural.primer_apellido')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => trim(($record->personaNatural->primer_apellido ?? '').' '.($record->personaNatural->segundo_apellido ?? ''))),

                TextColumn::make('personaNatural.numero_identificacion')
                    ->label('Identificación')
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->personaNatural
                        ? ($record->personaNatural->tipo_identificacion.' '.$record->personaNatural->numero_identificacion)
                        : 'N/A'),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),

                TextColumn::make('colaborador.fecha_ingreso')
                    ->label('Ingreso')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('colaborador.estado')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoCatalogo::options())
                    ->query(function (Builder $query, array $data): Builder {
                        if (! filled($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('colaborador', function (Builder $colaboradorQuery) use ($data): void {
                            $colaboradorQuery->where('estado', $data['value']);
                        });
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions(actions: [
                ActionGroup::make([
                    ViewAction::make()
                        ->modalWidth('7xl'),
                    EditAction::make()
                        ->modalWidth('7xl'),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])->icon(Heroicon::AdjustmentsVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
