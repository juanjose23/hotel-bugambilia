<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Schemas;

use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Shared\Imagen;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class EspacioInfolist
{
    public function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información General')
                ->icon(Heroicon::HomeModern)
                ->description('Datos básicos e identificación del espacio.')
                ->columns([
                    'default' => 1,
                    'md' => 4,
                ])
                ->schema([
                    TextEntry::make('codigo')
                        ->label('Código')
                        ->badge()
                        ->color('primary')
                        ->copyable()
                        ->icon(Heroicon::Hashtag)
                        ->weight(FontWeight::Bold),

                    TextEntry::make('nombre')
                        ->label('Nombre del Espacio')
                        ->icon(Heroicon::Tag)
                        ->weight(FontWeight::Bold),

                    TextEntry::make('tipo')
                        ->label('Tipo de Espacio')
                        ->badge()
                        ->color('success')
                        ->icon(fn ($state) => $state?->getIcon()),

                    TextEntry::make('padre.nombre')
                        ->label('Espacio Padre')
                        ->icon(Heroicon::FolderOpen)
                        ->placeholder('Espacio Principal (Sin padre)'),

                    TextEntry::make('ubicacion.nombre')
                        ->label('Ubicación Física')
                        ->icon(Heroicon::MapPin)
                        ->placeholder(fn ($record) => $record->padre?->ubicacion->nombre ?? 'Sin ubicación asignada'),

                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge()
                        ->color(fn ($state) => $state?->getColor() ?? 'gray')
                        ->icon(fn ($state) => $state?->getIcon()),

                    TextEntry::make('capacidad_personas')
                        ->label('Capacidad de Personas')
                        ->numeric()
                        ->icon(Heroicon::Users)
                        ->suffix(' personas')
                        ->weight(FontWeight::SemiBold),

                    TextEntry::make('web')
                        ->label('Visible en Portal Web')
                        ->badge()
                        ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                        ->formatStateUsing(fn (bool $state): string => $state ? 'Visible en Web' : 'Uso Interno')
                        ->icon(fn (bool $state): Heroicon => $state ? Heroicon::GlobeAlt : Heroicon::EyeSlash),

                    TextEntry::make('reservable')
                        ->label('Reservable')
                        ->badge()
                        ->color(fn (bool $state): string => $state ? 'info' : 'warning')
                        ->formatStateUsing(fn (bool $state): string => $state ? 'Reservable' : 'No Reservable')
                        ->icon(fn (bool $state): Heroicon => $state ? Heroicon::CalendarDays : Heroicon::LockClosed),

                    TextEntry::make('orden')
                        ->label('Orden de Clasificación')
                        ->numeric()
                        ->icon(Heroicon::ArrowDownCircle),
                ]),

            Section::make('Galería de Fotos')
                ->icon(Heroicon::Photo)
                ->description('Imágenes registradas del espacio.')
                ->collapsible()
                ->schema([
                    RepeatableEntry::make('imagenes')
                        ->hiddenLabel()
                        ->grid([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 3,
                        ])
                        ->schema([
                            ImageEntry::make('url')
                                ->hiddenLabel()
                                ->state(fn (Imagen $record): string => $record->url_completa)
                                ->imageHeight(180)
                                ->columnSpanFull()
                                ->extraImgAttributes([
                                    'class' => 'rounded-2xl object-cover w-full shadow-sm border border-gray-200 dark:border-gray-800 transition duration-300 hover:scale-[1.02] hover:shadow-lg',
                                    'style' => 'width: 100%; height: 180px; object-fit: cover;',
                                ]),
                        ])
                        ->placeholder('No hay imágenes registradas.'),
                ]),

            Section::make('Sub-espacios Asignados')
                ->icon(Heroicon::Squares2x2)
                ->description(fn ($record) => 'Mesas, zonas o áreas contenidas dentro de este espacio padre. Total: '.$record->hijos()->count().' sub-espacios.')
                ->visible(fn ($record) => $record->hijos()->exists())
                ->schema([
                    TextEntry::make('hijos_count')
                        ->hiddenLabel()
                        ->badge()
                        ->color('success')
                        ->icon(Heroicon::Squares2x2)
                        ->formatStateUsing(fn ($record) => $record->hijos()->count().' sub-espacios en total')
                        ->extraAttributes(['class' => 'mb-4']),

                    TextEntry::make('tab_hint')
                        ->hiddenLabel()
                        ->icon(Heroicon::ArrowTrendingDown)
                        ->placeholder('Gestiona los sub-espacios desde la pestaña "Sub-espacios" debajo de esta vista.'),
                ]),

            Section::make('Tarifas de Alquiler / Reserva')
                ->icon(Heroicon::CurrencyDollar)
                ->description('Costos de reserva o renta asignados a este espacio.')
                ->columns(2)
                ->schema([
                    TextEntry::make('tarifa_por_hora')
                        ->label('Tarifa por Hora')
                        ->money('NIO')
                        ->icon(Heroicon::Clock)
                        ->placeholder('No aplica'),

                    TextEntry::make('precio_base')
                        ->label('Precio Base por Reserva')
                        ->money('NIO')
                        ->icon(Heroicon::Ticket)
                        ->placeholder('No aplica'),
                ]),

            Section::make('Descripción')
                ->icon(Heroicon::DocumentText)
                ->description('Detalles y observaciones generales.')
                ->collapsible()
                ->schema([
                    TextEntry::make('descripcion')
                        ->hiddenLabel()
                        ->placeholder('Sin descripción registrada.')
                        ->prose(),
                ]),

            Section::make('Auditoría')
                ->icon(Heroicon::Clock)
                ->description('Información de trazabilidad y control.')
                ->columns(2)
                ->schema([
                    ...TimestampsInfolistEntry::make(format: 'd/m/Y H:i', since: true, withIcons: true, size: TextSize::Small),
                ]),
        ]);
    }
}
