<?php

namespace App\Filament\Resources\Auditoria\Audits\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use OwenIt\Auditing\Models\Audit;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(components: [
                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('user.name')
                    ->label('Modificado por')
                    ->formatStateUsing(callback: function ($state, $record) {
                        $user = $record->user;
                        if (! $user) {
                            return 'Sistema';
                        }
                        if ($user->persona?->primer_nombre) {
                            return $user->persona->primer_nombre.' '.$user->persona->segundo_nombre;
                        }

                        return $user->name;
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Evento')
                    ->options([
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored' => 'Restaurado',
                    ]),
                SelectFilter::make('auditable_type')
                    ->label('Modelo')
                    ->options(
                        Audit::query()
                            ->select('auditable_type')
                            ->distinct()
                            ->pluck('auditable_type', 'auditable_type')
                            ->mapWithKeys(fn ($type) => [
                                is_scalar($type) ? (string) $type : '' => is_string($type) ? class_basename($type) : '',
                            ])
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
