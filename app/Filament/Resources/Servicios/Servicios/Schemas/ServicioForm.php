<?php

namespace App\Filament\Resources\Servicios\Servicios\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Servicios\GenerarCodigoServicio;
use App\Interactors\Servicios\SincronizarGaleriaImagenes;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Servicios\ObtenerListadoHeroicons;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Stringable;

class ServicioForm
{
    public static function configure(Schema $schema): Schema
    {
        $generarCodigoServicio = app(GenerarCodigoServicio::class);
        $listadoHeroicons = app(ObtenerListadoHeroicons::class);
        $sincronizarGaleria = app(SincronizarGaleriaImagenes::class);

        return $schema
            ->components([
                Section::make('Información General del Servicio')
                    ->columns()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->default(fn (?Servicio $record) => $record ? $record->codigo : $generarCodigoServicio->ejecutar())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon(Heroicon::Key)
                            ->placeholder('SRV-XXXX'),

                        TextInput::make('nombre')
                            ->label('Nombre del Servicio')
                            ->required()
                            ->maxLength(100)
                            ->prefixIcon(Heroicon::Tag)
                            ->placeholder('Ej. Masaje Relajante'),

                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->placeholder('Seleccionar categoría')
                            ->relationship(
                                name: 'categoria',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'catalogoTipo',
                                    fn (Builder $q) => $q->where('codigo', CatalogoTipo::CATEGORIA_SERVICIO->value)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::ArchiveBox)
                            ->helperText('Categoría a la que pertenece el servicio.'),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoGeneral::options())
                            ->default(EstadoGeneral::Activo->value)
                            ->required()
                            ->prefixIcon(Heroicon::CheckCircle),

                        Toggle::make('web')
                            ->label('Mostrar en Web')
                            ->default(false)
                            ->inline(false)
                            ->helperText('Activar para que este servicio sea visible en el sitio web público.'),

                        Select::make('icono')
                            ->label('Icono Representativo (Sitio Web)')
                            ->options($listadoHeroicons->ejecutar())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->prefixIcon(function (?string $state): string {
                                if (! $state) {
                                    return 'heroicon-o-sparkles';
                                }
                                if (str_starts_with($state, 'heroicon-')) {
                                    return $state;
                                }

                                return match ($state) {
                                    'wifi' => 'heroicon-o-wifi',
                                    'coffee' => 'heroicon-o-cup-soda',
                                    'utensils', 'restaurant' => 'heroicon-o-building-storefront',
                                    'bar' => 'heroicon-o-cake',
                                    'pool', 'swimming' => 'heroicon-o-lifebuoy',
                                    'sparkles' => 'heroicon-o-sparkles',
                                    'car', 'parking' => 'heroicon-o-truck',
                                    'gym' => 'heroicon-o-trophy',
                                    'laundry', 'shirt' => 'heroicon-o-scissors',
                                    'concierge', 'bell' => 'heroicon-o-bell',
                                    'ac', 'wind' => 'heroicon-o-sun',
                                    'tv' => 'heroicon-o-computer-desktop',
                                    'bath' => 'heroicon-o-home-modern',
                                    'lock' => 'heroicon-o-lock-closed',
                                    'key' => 'heroicon-o-key',
                                    'sun' => 'heroicon-o-sun',
                                    'flame' => 'heroicon-o-fire',
                                    'gift' => 'heroicon-o-gift',
                                    'phone' => 'heroicon-o-phone',
                                    'bed' => 'heroicon-o-home',
                                    'calendar' => 'heroicon-o-calendar',
                                    'card' => 'heroicon-o-credit-card',
                                    'scissors' => 'heroicon-o-scissors',
                                    'plane' => 'heroicon-o-paper-airplane',
                                    'briefcase' => 'heroicon-o-briefcase',
                                    'map' => 'heroicon-o-map',
                                    default => 'heroicon-o-sparkles',
                                };
                            })
                            ->placeholder('Selecciona un icono emblemático para la página web')
                            ->helperText('Selecciona un icono representativo que se mostrará en el sitio web (mapeado automáticamente a Lucide Icons).'),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Escribe una descripción detallada sobre el servicio...')
                            ->columnSpanFull()
                            ->rows(4),

                        FileUpload::make('imagenes')
                            ->label('Galería de Imágenes')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('servicios/galeria')
                            ->maxFiles(5)
                            ->maxSize(4096)
                            ->helperText('Sube hasta 5 imágenes de alta calidad (16:9 recomendado) para la galería web.')
                            ->columnSpanFull()
                            ->afterStateHydrated(function (FileUpload $component, ?Servicio $record) {
                                if ($record) {
                                    $component->state(
                                        $record->imagenes()
                                            ->orderBy('orden')
                                            ->pluck('url')
                                            ->toArray()
                                    );
                                }
                            })
                            ->dehydrated(false)
                            ->saveRelationshipsUsing(function (Servicio $record, $state) use ($sincronizarGaleria) {
                                $imageUrls = is_array($state)
                                    ? array_values(array_filter(array_map(fn (mixed $val): string => is_scalar($val) || $val instanceof Stringable ? (string) $val : '', $state), fn (string $u): bool => $u !== ''))
                                    : [];
                                $sincronizarGaleria->execute($record, $imageUrls);
                            }),
                    ]),
            ]);
    }
}
