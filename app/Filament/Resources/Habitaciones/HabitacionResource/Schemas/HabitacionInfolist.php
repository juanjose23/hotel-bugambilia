<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Schemas;

use App\Models\Catalogos\Catalogo;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

class HabitacionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // 1. Información General de la Habitación (Ocupa todo el ancho en 3 columnas simétricas)
            Section::make('Información General')
                ->icon(Heroicon::HomeModern)
                ->description('Datos básicos e identificación de la habitación.')
                ->columns([
                    'default' => 1,
                    'md' => 3,
                ])
                ->schema([
                    TextEntry::make('codigo')
                        ->label('Código')
                        ->badge()
                        ->color('primary')
                        ->copyable()
                        ->icon(Heroicon::Key)
                        ->weight(FontWeight::Bold),

                    TextEntry::make('numero')
                        ->label('Número')
                        ->badge()
                        ->color('info')
                        ->copyable()
                        ->icon(Heroicon::Hashtag)
                        ->placeholder('Sin número')
                        ->weight(FontWeight::Medium),

                    TextEntry::make('nombre')
                        ->label('Nombre')
                        ->icon(Heroicon::Tag)
                        ->weight(FontWeight::Bold),

                    TextEntry::make('categoria.nombre')
                        ->label('Categoría')
                        ->badge()
                        ->color('success')
                        ->icon(Heroicon::RectangleStack),

                    TextEntry::make('ubicacion.nombre')
                        ->label('Ubicación')
                        ->icon(Heroicon::MapPin)
                        ->placeholder('Sin ubicación'),

                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge()
                        ->color(fn ($state) => $state?->color() ?? 'gray')
                        ->icon(fn ($state) => $state?->icon()),
                ]),

            // 2. Galería de Fotos (Diseño a todo lo ancho con cuadrícula responsiva)
            Section::make('Galería de Fotos')
                ->icon(Heroicon::Photo)
                ->description('Imágenes registradas de la habitación.')
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
                                ->disk('local')
                                ->imageHeight(180)
                                ->columnSpanFull()
                                ->extraImgAttributes([
                                    'class' => 'rounded-2xl object-cover w-full shadow-sm border border-gray-200 dark:border-gray-800 transition duration-300 hover:scale-[1.02] hover:shadow-lg',
                                    'style' => 'width: 100%; height: 180px; object-fit: cover;',
                                ]),
                        ])
                        ->placeholder('No hay imágenes registradas.'),
                ]),

            // 3. Descripción de la Habitación
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

            // 4. Capacidad y Distribución (3 columnas a lo ancho en una sola línea)
            Section::make('Capacidad y Espacio')
                ->icon(Heroicon::Users)
                ->description('Distribución y capacidad de ocupación.')
                ->columns([
                    'default' => 1,
                    'md' => 3,
                ])
                ->schema([
                    TextEntry::make('detalle.capacidad_adultos')
                        ->label('Adultos')
                        ->numeric()
                        ->icon(Heroicon::User)
                        ->suffix(' huéspedes')
                        ->weight(FontWeight::SemiBold),

                    TextEntry::make('detalle.capacidad_ninos')
                        ->label('Niños')
                        ->numeric()
                        ->icon(Heroicon::UserGroup)
                        ->suffix(' huéspedes')
                        ->weight(FontWeight::SemiBold),

                    TextEntry::make('detalle.medidas')
                        ->label('Área')
                        ->icon(Heroicon::ArrowsPointingOut)
                        ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2).' m²' : null)
                        ->placeholder('No especificada')
                        ->weight(FontWeight::Bold)
                        ->color('primary'),
                ]),

            // 5. Vistas Disponibles
            Section::make('Vistas')
                ->icon(Heroicon::Photo)
                ->description('Vistas panorámicas disponibles.')
                ->schema([
                    TextEntry::make('detalle.vistas')
                        ->hiddenLabel()
                        ->badge()
                        ->separator(', ')
                        ->color('info')
                        ->formatStateUsing(function (mixed $state): ?string {
                            if (blank($state)) {
                                return null;
                            }
                            $ids = is_array($state) ? $state : (array) json_decode((string) $state, true);
                            $catalogos = Cache::remember(
                                'catalogos_vistas',
                                now()->addHour(),
                                fn () => Catalogo::pluck('nombre', 'id')->toArray()
                            );

                            return collect($ids)
                                ->map(fn ($id) => $catalogos[$id] ?? null)
                                ->filter()
                                ->implode(', ');
                        })
                        ->placeholder('Sin vistas registradas'),
                ]),

            // 7. Auditoría y Control
            Section::make('Auditoría')
                ->icon(Heroicon::Clock)
                ->description('Información de trazabilidad y control.')
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Creado')
                        ->dateTime('d/m/Y H:i')
                        ->since()
                        ->icon(Heroicon::PlusCircle)
                        ->size(TextSize::Small),

                    TextEntry::make('updated_at')
                        ->label('Actualizado')
                        ->dateTime('d/m/Y H:i')
                        ->since()
                        ->icon(Heroicon::PencilSquare)
                        ->size(TextSize::Small),
                ]),
        ]);
    }
}
