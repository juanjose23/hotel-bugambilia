<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Filament\Shared\Forms\CategoriaSelect;
use App\Interactors\Habitaciones\GenerarCodigoHabitacion;
use App\Interactors\Habitaciones\GenerarSlugHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Support\CachedOptions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HabitacionForm
{
    public static function configure(Schema $schema): Schema
    {
        $generarCodigo = app(GenerarCodigoHabitacion::class);
        $generarSlug = app(GenerarSlugHabitacion::class);

        return $schema
            ->components([
                Section::make('Identificación de la Habitación')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon(Heroicon::Hashtag)
                            ->placeholder('101')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) use ($generarCodigo, $generarSlug) {
                                if (! $state) {
                                    return;
                                }
                                $set('codigo', $generarCodigo->ejecutar());
                                $set('slug', $generarSlug->ejecutar((string) $state));
                            }),

                        TextInput::make('codigo')
                            ->label('Código Interno')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon(Heroicon::Key)
                            ->placeholder('HAB-101')
                            ->helperText('Se auto-genera al ingresar el número. Puede editarlo.'),

                        Hidden::make('slug')
                            ->required(),

                        TextInput::make('nombre')
                            ->label('Nombre Descriptivo (Opcional)')
                            ->maxLength(100)
                            ->placeholder('Ej: Habitación 101 - Estándar Sencilla')
                            ->columnSpanFull(),
                    ]),

                Section::make('Clasificación y Ubicación')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        CategoriaSelect::make(CatalogoTipo::CATEGORIA_HABITACION)
                            ->required()
                            ->prefixIcon(Heroicon::Tag),

                        Select::make('ubicacion_id')
                            ->label('Ubicación / Nivel')
                            ->options(CachedOptions::ubicaciones())
                            ->searchable()
                            ->preload()
                            ->prefixIcon(Heroicon::BuildingOffice),

                        Select::make('estado')
                            ->label('Estado Operativo')
                            ->options(EstadoEspacio::options())
                            ->default(EstadoEspacio::Activa->value)
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::CheckCircle),
                    ]),

                Section::make('Descripción Comercial')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Detalles comercializables de la habitación...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Galería de Imágenes')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('imagenes')
                            ->label('Imágenes de la Habitación')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('habitaciones/galeria')
                            ->maxFiles(8)
                            ->maxSize(4096)
                            ->columnSpanFull()
                            ->afterStateHydrated(function (FileUpload $component, ?Habitacion $record) {
                                if ($record) {
                                    $component->state(
                                        $record->imagenes()
                                            ->orderBy('orden')
                                            ->pluck('url')
                                            ->toArray()
                                    );
                                }
                            }),
                    ]),
            ]);
    }
}
