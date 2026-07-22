<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\PromocionResource\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Promociones\GenerarCodigoPromocion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class PromocionForm
{
    public static function configure(Schema $schema): Schema
    {
        $generarCodigo = app(GenerarCodigoPromocion::class);

        return $schema
            ->components([
                Section::make('Datos de la Promoción')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->default(fn (?Promocion $record) => $record ? $record->codigo : $generarCodigo->ejecutar())
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon(Heroicon::Key)
                            ->placeholder('PROM-0001')
                            ->helperText('Código auto-generado. Puede personalizarlo.'),

                        TextInput::make('nombre')
                            ->label('Nombre de la Promoción / Paquete')
                            ->required()
                            ->maxLength(150)
                            ->prefixIcon(Heroicon::Tag)
                            ->placeholder('Ej: Escapada Romántica Todo Incluido'),

                        Select::make('tipo_promocion_id')
                            ->label('Tipo de Promoción')
                            ->placeholder('Seleccionar tipo')
                            ->relationship(
                                name: 'tipo',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'catalogoTipo',
                                    fn (Builder $q) => $q->where('codigo', CatalogoTipo::TIPO_PROMOCION->value)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->prefixIcon(Heroicon::Tag),

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
                            ->helperText('Activar para mostrar esta promoción en el portal público.'),

                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),

                        DatePicker::make('fecha_fin')
                            ->label('Fecha de Fin')
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),

                        TextInput::make('precio_paquete')
                            ->label('Precio Total del Paquete')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('C$')
                            ->prefixIcon(Heroicon::Banknotes)
                            ->placeholder('1500.00')
                            ->helperText('Precio global del combo / paquete promocional.'),

                        TextInput::make('descuento_porcentaje')
                            ->label('Descuento (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->prefixIcon(Heroicon::ArrowTrendingDown),

                        TextInput::make('descuento_monto')
                            ->label('Descuento Adicional (Monto)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('C$')
                            ->prefixIcon(Heroicon::CurrencyDollar),

                        TextInput::make('orden')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->prefixIcon(Heroicon::QueueList),
                    ]),

                Section::make('Contenido')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Describe el paquete promocional...')
                            ->columnSpanFull()
                            ->rows(3),

                        Textarea::make('condiciones')
                            ->label('Términos y Condiciones')
                            ->placeholder('Condiciones aplicables...')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),

                Section::make('Imagen de la Promoción')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('imagenes')
                            ->label('Galería de Imágenes')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('promociones')
                            ->maxFiles(3)
                            ->maxSize(4096)
                            ->columnSpanFull()
                            ->afterStateHydrated(function (FileUpload $component, ?Promocion $record) {
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
                            ->saveRelationshipsUsing(function (Promocion $record, $state) {
                                $imageUrls = array_map(fn (mixed $val): string => is_string($val) || $val instanceof \Stringable ? (string) $val : '', is_array($state) ? $state : []);
                                /** @var array<int, string> $existingUrls */
                                $existingUrls = $record->imagenes()->pluck('url')->map(fn (mixed $u): string => is_scalar($u) ? strval($u) : '')->toArray();
                                $toDelete = array_diff($existingUrls, $imageUrls);
                                if ($toDelete !== []) {
                                    $record->imagenes()->whereIn('url', $toDelete)->delete();
                                }
                                foreach ($imageUrls as $index => $url) {
                                    if ($url === '') {
                                        continue;
                                    }
                                    $record->imagenes()->updateOrCreate(
                                        ['url' => $url],
                                        ['orden' => $index + 1],
                                    );
                                }
                            }),
                    ]),

                Section::make('Servicios y Habitaciones Incluidos en el Paquete')
                    ->description('Agregue los ítems que componen este paquete único.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship('items')
                            ->schema([
                                Select::make('item_type')
                                    ->label('Tipo')
                                    ->options([
                                        Servicio::class => 'Servicio',
                                        Habitacion::class => 'Habitación',
                                        Espacio::class => 'Espacio (Restaurante, Salón, etc.)',
                                    ])
                                    ->required()
                                    ->live()
                                    ->columnSpan(6),

                                Select::make('item_id')
                                    ->label('Elemento Incluido')
                                    ->options(function (Get $get) {
                                        $type = $get('item_type');
                                        if (! is_string($type)) {
                                            return [];
                                        }
                                        if ($type === Servicio::class) {
                                            return Servicio::activos()->pluck('nombre', 'id')->toArray();
                                        }
                                        if ($type === Habitacion::class) {
                                            return Habitacion::activas()->pluck('nombre', 'id')->toArray();
                                        }
                                        if ($type === Espacio::class) {
                                            return Espacio::where('estado', 1)->pluck('nombre', 'id')->toArray();
                                        }

                                        return [];
                                    })
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(6),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->collapsible()
                            ->addActionLabel('Agregar Elemento al Paquete'),
                    ]),
            ]);
    }
}
