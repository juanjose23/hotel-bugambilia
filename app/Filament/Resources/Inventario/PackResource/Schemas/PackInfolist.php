<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PackInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Pack')
                    ->icon(Heroicon::Cube)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre'),
                        TextEntry::make('categoria.nombre')
                            ->label('Categoría')
                            ->placeholder('Sin categoría'),
                        TextEntry::make('marca.nombre')
                            ->label('Marca')
                            ->placeholder('Sin marca'),
                        TextEntry::make('unidadMedida.nombre')
                            ->label('Unidad de Medida')
                            ->placeholder('No definida'),
                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Sin descripción')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
