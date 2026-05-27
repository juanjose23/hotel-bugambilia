<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\RegistroIndividualizacion\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistroIndividualizacionForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detalles de Individualización')
                ->description('Resumen del lote de compras pendiente de individualizar como activos fijos.')
                ->schema([
                    TextEntry::make('recepcion_codigo')
                        ->label('Código de Recepción')
                        ->getStateUsing(fn ($record) => $record->recepcionItem->recepcion->codigo ?? '-'),

                    TextEntry::make('producto_nombre')
                        ->label('Producto')
                        ->getStateUsing(fn ($record) => $record->producto->nombre ?? '-'),

                    TextEntry::make('cantidad_total')
                        ->label('Cantidad Total')
                        ->getStateUsing(fn ($record) => (string) ($record->cantidad_total ?? 0)),

                    TextEntry::make('cantidad_registrada')
                        ->label('Cantidad Registrada')
                        ->getStateUsing(fn ($record) => (string) ($record->cantidad_registrada ?? 0)),

                    TextEntry::make('estado')
                        ->label('Estado')
                        ->getStateUsing(fn ($record) => $record->estado?->label() ?? '-'),
                ])
                ->columns(2),
        ]);
    }
}
