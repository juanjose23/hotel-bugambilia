<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
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
            ->columns(2)
            ->components([

                Section::make('Información del Catálogo')
                    ->description('Detalles principales y clasificación en el sistema.')
                    ->icon(Heroicon::InformationCircle)
                    ->columns(2)
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
                            ->formatStateUsing(fn ($state) => EstadoGeneral::labelFor($state))
                            ->color(fn ($state) => EstadoGeneral::colorFor($state))
                            ->icon(fn ($state) => $state ? Heroicon::CheckCircle : Heroicon::XCircle)
                            ->columnSpan(2),

                        TextEntry::make('codigo')
                            ->label('Código')
                            ->badge()
                            ->color('gray')
                            ->icon(Heroicon::Hashtag)
                            ->columnSpan(1),

                        TextEntry::make('catalogoTipo.nombre')
                            ->label('Tipo')
                            ->icon(Heroicon::Square2Stack)
                            ->weight(FontWeight::Medium)
                            ->columnSpan(1),

                        TextEntry::make('padre.nombre')
                            ->label('Padre')
                            ->placeholder('Registro Raíz')
                            ->icon(Heroicon::Link)
                            ->color('gray')
                            ->columnSpan(1),

                        TextEntry::make('orden')
                            ->label('Orden')
                            ->icon(Heroicon::Bars3BottomLeft)
                            ->numeric()
                            ->columnSpan(1),
                    ])
                    ->columnSpan(1),

                // NODO RAÍZ (Registro Actual)
                Group::make([
                    Section::make('Descripción')
                        ->icon(Heroicon::DocumentText)
                        ->schema([
                            TextEntry::make('descripcion')
                                ->hiddenLabel()
                                ->placeholder('Sin descripción registrada.')
                                ->prose(),
                        ]),

                    Section::make('Trazabilidad')
                        ->icon(Heroicon::Clock)
                        ->schema([
                            ...TimestampsInfolistEntry::make(format: 'd/m/Y H:i', withIcons: true, size: TextSize::Small),
                        ]),
                ])
                    ->columnSpan(1),

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
                                'style' => 'max-width: 24rem; margin: 1.5rem auto; padding: 1.5rem; border-radius: 1.5rem; border: 2px solid var(--primary-500, #ec4899); background: rgba(236, 72, 153, 0.05); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); text-align: center;',
                            ]),

                        // LÍNEA DE CONEXIÓN VERTICAL
                        Group::make([])
                            ->visible(fn ($record) => $record?->children()->exists())
                            ->extraAttributes([
                                'style' => 'height: 3rem; width: 4px; background: var(--primary-500, #ec4899); margin: 0 auto;',
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
                                // NODO RAÍZ (Registro Actual)
                                Group::make([
                                    TextEntry::make('nombre')
                                        ->hiddenLabel()
                                        ->weight(FontWeight::Bold)
                                        ->color('primary')
                                        ->icon(Heroicon::ChevronRight),

                                    // NODO RAÍZ (Registro Actual)
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
                                        'style' => 'padding: 1rem; border-radius: 1rem; border: 1px solid rgba(156, 163, 175, 0.2); background: rgba(255, 255, 255, 0.02); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); text-align: center; position: relative;',
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
