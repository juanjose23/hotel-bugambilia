<?php

namespace App\Filament\Resources\Servicios\Servicios\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Servicios\ServicioEstado;
use App\Models\Servicios\Servicio;
use App\UseCases\Servicios\Mutations\GenerarCodigoServicio;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ServicioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General del Servicio')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->default(fn (?Servicio $record) => $record ? $record->codigo : app(GenerarCodigoServicio::class)->ejecutar())
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
                            ->options(ServicioEstado::options())
                            ->default(ServicioEstado::Activo->value)
                            ->required()
                            ->prefixIcon(Heroicon::CheckCircle),

                        Select::make('icono')
                            ->label('Icono Representativo')
                            ->options(self::getHeroicons())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->prefixIcon(fn ($state) => $state ?? 'heroicon-o-sparkles')
                            ->placeholder('Selecciona un icono emblemático')
                            ->helperText('Icono visual que aparecerá listado al lado del servicio en el sitio web.'),

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
                            ->saveRelationshipsUsing(function (Servicio $record, $state) {
                                $state = is_array($state) ? $state : [];

                                // 1. Eliminar imágenes que ya no están presentes en el estado
                                /** @var array<string> $existingImages */
                                $existingImages = $record->imagenes()->pluck('url')->map(fn ($u) => is_scalar($u) ? (string) $u : '')->toArray();
                                /** @var array<string> $stateImages */
                                $stateImages = collect((array) $state)->map(fn ($u) => is_scalar($u) ? (string) $u : '')->toArray();
                                $toDelete = array_diff($existingImages, $stateImages);
                                if (! empty($toDelete)) {
                                    $record->imagenes()->whereIn('url', $toDelete)->delete();
                                }

                                // 2. Guardar y reordenar las imágenes actuales
                                foreach ($state as $index => $url) {
                                    $record->imagenes()->updateOrCreate(
                                        ['url' => $url],
                                        ['orden' => $index + 1]
                                    );
                                }
                            }),
                    ]),
            ]);
    }

    /**
     * Obtiene y almacena en caché todos los iconos Outline de Heroicons
     * disponibles en el paquete blade-heroicons.
     *
     * @return array<string, string>
     */
    protected static function getHeroicons(): array
    {
        return cache()->rememberForever('heroicons_outline_list', function () {
            $svgPath = base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg');
            $icons = [];

            if (is_dir($svgPath)) {
                $files = scandir($svgPath);
                if ($files !== false) {
                    foreach ($files as $file) {
                        if (str_starts_with($file, 'o-') && str_ends_with($file, '.svg')) {
                            $name = substr($file, 2, -4); // Quitar 'o-' y '.svg'
                            $key = 'heroicon-o-'.$name;
                            $label = ucwords(str_replace('-', ' ', $name));
                            $icons[$key] = $label;
                        }
                    }
                }
            }

            asort($icons);

            return $icons;
        });
    }
}
