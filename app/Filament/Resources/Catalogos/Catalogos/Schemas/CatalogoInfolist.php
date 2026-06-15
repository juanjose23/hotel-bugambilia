<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Schemas;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Filament\Resources\Shared\InfolistTimestamps;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class CatalogoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Catálogo')
                    ->description('Detalles principales y clasificación en el sistema.')
                    ->icon(Heroicon::InformationCircle)
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('nombre')
                                    ->label('Nombre')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->icon(Heroicon::Tag)
                                    ->columnSpan(2),

                                TextEntry::make('estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => EstadoCatalogo::labelFor($state))
                                    ->color(fn ($state) => EstadoCatalogo::colorFor($state))
                                    ->icon(fn ($state) => $state ? Heroicon::CheckCircle : Heroicon::XCircle)
                                    ->columnSpan(2),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('codigo')
                                    ->label('Código')
                                    ->badge()
                                    ->color('gray')
                                    ->icon(Heroicon::Hashtag),

                                TextEntry::make('catalogoTipo.nombre')
                                    ->label('Tipo')
                                    ->icon(Heroicon::Square2Stack)
                                    ->weight(FontWeight::Medium),

                                TextEntry::make('padre.nombre')
                                    ->label('Padre')
                                    ->placeholder('Registro Raíz')
                                    ->icon(Heroicon::Link)
                                    ->color('gray'),

                                TextEntry::make('orden')
                                    ->label('Orden')
                                    ->icon(Heroicon::Bars3BottomLeft)
                                    ->numeric(),
                            ]),
                    ]),

                // DESCRIPCIÓN Y TRAZABILIDAD
                Grid::make(3)
                    ->schema([
                        Section::make('Descripción')
                            ->icon(Heroicon::DocumentText)
                            ->schema([
                                TextEntry::make('descripcion')
                                    ->hiddenLabel()
                                    ->placeholder('Sin descripción registrada.')
                                    ->prose(),
                            ])
                            ->columnSpan(2),

                        Section::make('Trazabilidad')
                            ->icon(Heroicon::Clock)
                            ->schema([
                                ...InfolistTimestamps::make(format: 'd/m/Y H:i', withIcons: true, size: TextSize::Small),
                            ])
                            ->columnSpan(1),
                    ]),

                // ÁRBOL JERÁRQUICO VISUAL (Sitemap Style)
                Section::make('Mapa de Estructura')
                    ->description('Representación visual de la jerarquía de este elemento.')
                    ->schema([
                        // NODO RAÍZ (Registro Actual)
                        Group::make([
                            TextEntry::make('nombre_raiz')
                                ->state(fn ($record) => $record->nombre)
                                ->hiddenLabel()
                                ->weight(FontWeight::Black)
                                ->size(TextSize::Large)
                                ->color('primary')
                                ->icon(Heroicon::FolderOpen)
                                ->alignCenter(),
                        ])
                            ->extraAttributes([
                                'class' => 'max-w-md mx-auto p-6 rounded-3xl border-2 border-primary-500 bg-primary-50/50 dark:bg-primary-950/20 shadow-lg text-center',
                            ]),

                        // LÍNEA DE CONEXIÓN VERTICAL
                        Group::make([])
                            ->visible(fn ($record) => $record?->children()->exists())
                            ->extraAttributes([
                                'class' => 'h-12 w-1 bg-primary-500 mx-auto',
                            ]),
                        RepeatableEntry::make('children')
                            ->label('Subcategorias')
                            ->visible(fn ($record) => $record?->children()->exists())
                            ->grid([
                                'default' => 1,
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->schema([
                                Group::make([
                                    TextEntry::make('nombre')
                                        ->hiddenLabel()
                                        ->weight(FontWeight::Bold)
                                        ->color('primary')
                                        ->icon(Heroicon::ChevronRight),

                                    Group::make([
                                        TextEntry::make('codigo')
                                            ->badge()
                                            ->color('gray')
                                            ->size(TextSize::ExtraSmall),

                                        TextEntry::make('cantidad_hijos')
                                            ->badge()
                                            ->color('info')
                                            ->formatStateUsing(fn ($state) => "{$state} ramas")
                                            ->visible(fn ($state) => $state > 0),
                                    ])
                                        ->columns(2),
                                ])
                                    ->extraAttributes([
                                        'class' => 'p-4 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm text-center relative before:absolute before:-top-6 before:left-1/2 before:w-px before:h-6 before:bg-primary-500',
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
