<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorSalario\Tables;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\ColaboradorNombreColumn;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use App\Filament\Shared\Filters\FiltroEliminados;
use App\Repository\Models\Colaboradores\ColaboradorSalario;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ColaboradorSalarioTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitle(fn (ColaboradorSalario $record): string => $record->colaborador->codigo ?? 'Salario')
            ->columns([
                ColaboradorNombreColumn::make('colaborador.persona.nombre_completo'),
                MontoMonedaColumn::make('salario')
                    ->label('Salario'),
                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->placeholder('Activo'),
                EstadoBadgeColumn::make(EstadoGeneral::class),
            ])
            ->filters([
                FiltroEliminados::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading('Editar salario')
                        ->modalWidth('2xl'),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
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
