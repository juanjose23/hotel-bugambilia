<?php

namespace App\Filament\Resources\Auditoria\Audits\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Detalles del registro')
                    ->columns()
                    ->schema([
                        TextEntry::make('event')
                            ->label('Evento')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-s-document-text')
                            ->hint('Tipo de acción registrada'),

                        TextEntry::make('user.name')
                            ->label('Modificado por')
                            ->icon('heroicon-s-user')
                            ->formatStateUsing(callback: fn ($state, $record) => $record->user?->persona->primer_nombre
                                ?? $record->user->name
                                ?? 'Sistema'
                            )
                            ->hint('Usuario que realizó el cambio'),

                        TextEntry::make('created_at')
                            ->label('Fecha')
                            ->dateTime()
                            ->icon('heroicon-s-calendar')
                            ->color('secondary')
                            ->columnSpanFull()
                            ->hint('Fecha y hora del evento'),
                    ]),

                RepeatableEntry::make('timeline')
                    ->label('Cambios (Línea de tiempo)')
                    ->columnSpanFull()
                    ->state(function ($record) {
                        $old = $record->old_values ?? [];
                        $new = $record->new_values ?? [];

                        $timeline = [];

                        foreach ($new as $key => $value) {
                            $oldValue = $old[$key] ?? null;

                            if ($oldValue != $value) {
                                $timeline[] = [
                                    'field' => $key,
                                    'old' => $oldValue,
                                    'new' => $value,
                                ];
                            }
                        }

                        return $timeline;
                    })
                    ->grid(3)
                    ->schema([
                        TextEntry::make('field')
                            ->label('Campo')
                            ->badge()
                            ->color('primary')
                            ->hint('Nombre del atributo modificado'),

                        TextEntry::make('old')
                            ->label('Antes')
                            ->color('danger')
                            ->icon('heroicon-s-minus')
                            ->hint('Valor anterior'),

                        TextEntry::make('new')
                            ->label('Después')
                            ->color('success')
                            ->icon('heroicon-s-plus')
                            ->hint('Valor nuevo'),
                    ]),
            ]);
    }
}
