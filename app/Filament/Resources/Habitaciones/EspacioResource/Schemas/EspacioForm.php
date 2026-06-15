<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Schemas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoCocina;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\HabitacionesEspacios\TipoServicioEspacio;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

class EspacioForm
{
    public function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información General')
                    ->columnSpanFull()
                    ->description('Datos principales para identificar y clasificar el espacio físico')
                    ->icon(Heroicon::InformationCircle)
                    ->columns(3)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del Espacio')
                            ->placeholder('Ej. Mesa 5, Gran Salón Real, Gimnasio PB')
                            ->prefixIcon(Heroicon::Tag)
                            ->required()
                            ->maxLength(150)
                            ->columnSpan(2),

                        TextInput::make('codigo')
                            ->label('Código Único')
                            ->placeholder('Ej. MESA-05, SALON-A')
                            ->prefixIcon(Heroicon::Hashtag)
                            ->maxLength(50)
                            ->unique(table: 'espacios', column: 'codigo', ignoreRecord: true)
                            ->helperText('Si se deja vacío, se generará automáticamente con el formato {PREFIJO}-{NNNN}. Útil para sub-espacios.')
                            ->columnSpan(1),

                        Select::make('tipo')
                            ->label('Tipo de Espacio')
                            ->placeholder('Seleccione tipo')
                            ->options(TipoEspacio::options())
                            ->required()
                            ->live()
                            ->native(false)
                            ->prefixIcon(Heroicon::Squares2x2)
                            ->columnSpan(1),

                        Select::make('padre_id')
                            ->label('Espacio Padre (Contenedor)')
                            ->placeholder('Ninguno (Es espacio principal)')
                            ->options(function ($record) {
                                $query = Espacio::query()->with('padre');
                                if ($record) {
                                    $query->where('id', '!=', $record->id)
                                        ->where(function ($q) use ($record) {
                                            $q->whereNull('padre_id')
                                                ->orWhere('padre_id', '!=', $record->id);
                                        });
                                }

                                return $query->whereIn('tipo', [
                                    TipoEspacio::RESTAURANTE->value,
                                    TipoEspacio::GYM->value,
                                    TipoEspacio::SALON->value,
                                    TipoEspacio::SPA->value,
                                    TipoEspacio::OTRO->value,
                                ])
                                    ->get()
                                    ->mapWithKeys(fn (Espacio $e) => [$e->id => $e->getNombreCompleto()])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->prefixIcon(Heroicon::FolderOpen)
                            ->columnSpan(1),

                        Select::make('ubicacion_id')
                            ->label('Ubicación Física')
                            ->placeholder('Seleccione ubicación')
                            ->options(function () {
                                $ubicaciones = Cache::remember('espacio_form:ubicaciones', 3600, function () {
                                    return Ubicacion::query()
                                        ->with('padre.padre.padre')
                                        ->get();
                                });

                                return $ubicaciones->mapWithKeys(function (Ubicacion $u) {
                                    $path = $u->nombre;
                                    $p = $u->padre;
                                    while ($p !== null) {
                                        $path = $p->nombre.' > '.$path;
                                        $p->loadMissing('padre.padre');
                                        $p = $p->padre;
                                    }

                                    return [$u->id => $path];
                                })->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->prefixIcon(Heroicon::MapPin)
                            ->helperText('Si tiene un Espacio Padre, heredará automáticamente su ubicación física.')
                            ->columnSpan(1),

                        Select::make('estado')
                            ->label('Estado Inicial / Actual')
                            ->placeholder('Seleccione estado')
                            ->options(EstadoEspacio::class)
                            ->default(EstadoEspacio::Disponible->value)
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::ArrowPath)
                            ->columnSpan(1),

                        TextInput::make('capacidad_personas')
                            ->label('Capacidad Máxima')
                            ->placeholder('Ej. 4')
                            ->prefixIcon(Heroicon::Users)
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->columnSpan(1),

                        TextInput::make('orden')
                            ->label('Orden de Clasificación')
                            ->placeholder('Ej. 0')
                            ->prefixIcon(Heroicon::ArrowDownCircle)
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ]),

                Section::make('Configuración Específica del Tipo')
                    ->columnSpanFull()
                    ->description('Atributos específicos y dinámicos según el tipo de espacio seleccionado')
                    ->icon(Heroicon::WrenchScrewdriver)
                    ->visible(fn ($get) => in_array($get('tipo'), [
                        TipoEspacio::MESA->value,
                        TipoEspacio::SALON->value,
                        TipoEspacio::GYM->value,
                        TipoEspacio::RESTAURANTE->value,
                        TipoEspacio::SPA->value,
                        TipoEspacio::PISCINA->value,
                        TipoEspacio::CANCHA->value,
                    ]))
                    ->schema([
                        // ─── Mesa de Restaurante/Bar ─────────────────────
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                Select::make('meta_datos.tipo_mesa')
                                    ->label('Forma / Tipo de la Mesa')
                                    ->placeholder('Seleccione forma')
                                    ->options([
                                        'redonda' => 'Redonda',
                                        'cuadrada' => 'Cuadrada',
                                        'rectangular' => 'Rectangular',
                                        'barra' => 'Espacio de Barra / Taburete',
                                    ])
                                    ->native(false)
                                    ->prefixIcon(Heroicon::TableCells),

                                Select::make('meta_datos.zona_restaurante')
                                    ->label('Zona de Restaurante')
                                    ->placeholder('Seleccione zona')
                                    ->options([
                                        'interior' => 'Salón Interior',
                                        'terraza' => 'Terraza / Aire Libre',
                                        'vip' => 'Zona Reservada / VIP',
                                        'barra' => 'Barra de Tragos',
                                    ])
                                    ->native(false)
                                    ->prefixIcon(Heroicon::Map),
                            ])
                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::MESA->value),

                        // ─── Salón de Eventos ────────────────────────────
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('meta_datos.metros_cuadrados')
                                    ->label('Metros Cuadrados útiles (m²)')
                                    ->placeholder('Ej. 150')
                                    ->numeric()
                                    ->suffix('m²')
                                    ->prefixIcon(Heroicon::ArrowsPointingOut),

                                CheckboxList::make('meta_datos.equipamiento_incluido')
                                    ->label('Equipamiento Disponible')
                                    ->options([
                                        'proyector' => 'Proyector HD y Pantalla',
                                        'sonido' => 'Consola y Microfonía de Sonido',
                                        'clima' => 'Climatización Central / AC',
                                        'pizarra' => 'Pizarra Ejecutiva / Smartboard',
                                        'luces' => 'Iluminación Regulable para Eventos',
                                    ])
                                    ->columns(2),
                            ])
                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::SALON->value),

                        // ─── Gimnasio / Área de Fitness ──────────────────
                        Grid::make()
                            ->columns(1)
                            ->schema([
                                Textarea::make('meta_datos.restricciones_gimnasio')
                                    ->label('Políticas / Restricciones de Acceso')
                                    ->placeholder('Ej. Uso obligatorio de toalla personal, calzado deportivo adecuado...')
                                    ->rows(3),
                            ])
                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::GYM->value),

                        // ─── Restaurante / Bar ───────────────────────────
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                Select::make('meta_datos.tipo_cocina')
                                    ->label('Tipo de Cocina')
                                    ->placeholder('Seleccione tipo')
                                    ->options(TipoCocina::options())
                                    ->native(false)
                                    ->prefixIcon(Heroicon::Fire),

                                Select::make('meta_datos.tipo_servicio')
                                    ->label('Tipo de Servicio')
                                    ->placeholder('Seleccione servicio')
                                    ->options(TipoServicioEspacio::options())
                                    ->native(false)
                                    ->prefixIcon(Heroicon::UserGroup),

                                TextInput::make('meta_datos.horario_comida')
                                    ->label('Horario de Comida')
                                    ->placeholder('Ej. 07:00 - 22:00')
                                    ->prefixIcon(Heroicon::Clock),

                                TextInput::make('meta_datos.capacidad_mesas')
                                    ->label('Capacidad Total en Mesas')
                                    ->placeholder('Ej. 30')
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix(' mesas')
                                    ->prefixIcon(Heroicon::TableCells),
                            ])
                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::RESTAURANTE->value),

                        // ─── Spa / Cabina de Masajes ─────────────────────
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                Select::make('meta_datos.tipo_spa')
                                    ->label('Tipo de Servicio SPA')
                                    ->placeholder('Seleccione tipo')
                                    ->options([
                                        'masajes' => 'Masajes',
                                        'sauna' => 'Sauna',
                                        'hidroterapia' => 'Hidroterapia',
                                        'belleza' => 'Cabina de Belleza / Estética',
                                    ])
                                    ->native(false)
                                    ->prefixIcon(Heroicon::Sparkles),

                                TextInput::make('meta_datos.capacidad_simultanea')
                                    ->label('Capacidad Simultánea')
                                    ->placeholder('Ej. 4')
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix(' personas')
                                    ->prefixIcon(Heroicon::Users),

                                CheckboxList::make('meta_datos.equipamiento_spa')
                                    ->label('Equipamiento Disponible')
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->options([
                                        'camilla' => 'Camilla Profesional',
                                        'sauna' => 'Sauna Seca / Vapor',
                                        'jacuzzi' => 'Jacuzzi / Hidromasaje',
                                        'ducha_hidro' => 'Ducha de Hidroterapia',
                                        'vapor' => 'Baño de Vapor / Turco',
                                    ]),
                            ])
                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::SPA->value),

                        // ─── Área de Piscina ─────────────────────────────
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                Select::make('meta_datos.tipo_piscina')
                                    ->label('Tipo de Piscina')
                                    ->placeholder('Seleccione tipo')
                                    ->options([
                                        'principal' => 'Piscina Principal',
                                        'ninos' => 'Piscina de Niños',
                                        'hidromasaje' => 'Hidromasaje / Jacuzzi',
                                        'mixta' => 'Mixta (Principal + Niños)',
                                    ])
                                    ->native(false)
                                    ->prefixIcon(Heroicon::Sun),

                                TextInput::make('meta_datos.camastros')
                                    ->label('Cantidad de Camastros')
                                    ->placeholder('Ej. 20')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix(' camastros')
                                    ->prefixIcon(Heroicon::Squares2x2),

                                TextInput::make('meta_datos.horario_operacion')
                                    ->label('Horario de Operación')
                                    ->placeholder('Ej. 06:00 - 22:00')
                                    ->prefixIcon(Heroicon::Clock),

                                Toggle::make('meta_datos.toallas_incluidas')
                                    ->label('Toallas Incluidas')
                                    ->inline(false)
                                    ->default(true),
                            ])
                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::PISCINA->value),

                        // ─── Cancha Deportiva ────────────────────────────
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                Select::make('meta_datos.tipo_cancha')
                                    ->label('Tipo de Cancha')
                                    ->placeholder('Seleccione tipo')
                                    ->options([
                                        'tenis' => 'Tenis',
                                        'futbol' => 'Fútbol',
                                        'baloncesto' => 'Baloncesto',
                                        'voleibol' => 'Voleibol',
                                        'squash' => 'Squash',
                                        'multiusos' => 'Multiusos',
                                    ])
                                    ->native(false)
                                    ->prefixIcon(Heroicon::Flag),

                                Select::make('meta_datos.superficie')
                                    ->label('Tipo de Superficie')
                                    ->placeholder('Seleccione superficie')
                                    ->options([
                                        'cesped' => 'Césped Natural / Sintético',
                                        'cemento' => 'Cemento / Hormigón',
                                        'arcilla' => 'Arcilla',
                                        'sintetico' => 'Sintético / Poliuretano',
                                    ])
                                    ->native(false)
                                    ->prefixIcon(Heroicon::Square3Stack3d),

                                Toggle::make('meta_datos.iluminacion_nocturna')
                                    ->label('Iluminación Nocturna')
                                    ->inline(false)
                                    ->default(false),
                            ])
                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::CANCHA->value),
                    ]),

                Section::make('Descripción')
                    ->columnSpanFull()
                    ->description('Notas, detalles especiales o especificaciones del espacio')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        RichEditor::make('descripcion')
                            ->hiddenLabel()
                            ->placeholder('Detalles de decoración, vistas, facilidades o indicaciones especiales del espacio...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
