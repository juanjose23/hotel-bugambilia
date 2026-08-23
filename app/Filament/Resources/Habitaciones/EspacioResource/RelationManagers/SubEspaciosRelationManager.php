<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Filament\Resources\Habitaciones\EspacioResource\Schemas\EspacioForm;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Espacios\GenerarCodigoSubEspacio;
use App\Interactors\Espacios\ValidarCapacidadMesas;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Queries\Espacios\ConsultarCapacidadMesas;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use InvalidArgumentException;
use OverflowException;

class SubEspaciosRelationManager extends RelationManager
{
    protected static string $relationship = 'hijos';

    protected static ?string $title = 'Sub-espacios';

    protected static ?string $label = 'Sub-espacio';

    protected static ?string $pluralLabel = 'Sub-espacios';

    protected static BackedEnum|string|null $icon = Heroicon::Squares2x2;

    public function form(Schema $schema): Schema
    {
        return app(EspacioForm::class)->configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['padre', 'ubicacion']))
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('info')
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('capacidad_personas')
                    ->label('Capacidad')
                    ->alignCenter()
                    ->sortable()
                    ->suffix(' pers.'),

                EstadoBadgeColumn::make(EstadoEspacio::class)
                    ->sortable(),
            ])
            ->defaultSort('orden')
            ->filters([
                SelectFilter::make('tipo')
                    ->options(TipoEspacio::options()),
                FiltroEstado::make(EstadoEspacio::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::Plus)
                    ->before(function (CreateAction $action, ValidarCapacidadMesas $validarCapacidadMesas, ConsultarCapacidadMesas $consultarCapacidadMesas) {
                        /** @var Espacio $padre */
                        $padre = $this->getOwnerRecord();

                        // Solo validar capacidad cuando el padre es un RESTAURANTE
                        if ($padre->tipo !== TipoEspacio::RESTAURANTE) {
                            return;
                        }

                        $padreKey = $padre->getKey();
                        $restauranteId = is_numeric($padreKey) ? (int) $padreKey : 0;

                        try {
                            $validarCapacidadMesas->execute(
                                restauranteId: $restauranteId,
                                crearSiValida: false,
                            );
                        } catch (OverflowException $e) {
                            $capacidad = $consultarCapacidadMesas->execute($restauranteId);

                            Notification::make()
                                ->title('Capacidad máxima de mesas alcanzada')
                                ->body(
                                    'Este restaurante tiene configurado un límite de '
                                    ."{$capacidad['capacidad_configurada']} mesas y ya cuenta con "
                                    ."{$capacidad['mesas_activas']} registradas. "
                                    .'Actualice la capacidad en la configuración del restaurante antes de agregar más mesas.'
                                )
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->halt();
                        } catch (InvalidArgumentException $e) {
                            logger()->error($e->getMessage());
                            Notification::make()
                                ->title('Error')
                                ->body('Ocurrió un error al validar la capacidad del restaurante.')
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    })
                    ->mutateDataUsing(function (array $data, GenerarCodigoSubEspacio $generarCodigoSubEspacio): array {
                        $data['padre_id'] = $this->getOwnerRecord()->getKey();

                        if (empty($data['codigo'])) {
                            $tipo = isset($data['tipo'])
                                ? (is_string($data['tipo']) ? TipoEspacio::from($data['tipo']) : $data['tipo'])
                                : TipoEspacio::OTRO;

                            $data['codigo'] = $generarCodigoSubEspacio->execute($tipo);
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('Ver detalles')
                    ->icon(Heroicon::Eye)
                    ->iconButton()
                    ->modalHeading(fn ($record) => "Detalles: {$record->nombre}")
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->fillForm(fn ($record) => $record->toArray())
                    ->schema([
                        Tabs::make('detalles')->columnSpanFull()->tabs([
                            Tab::make('Información General')
                                ->icon(Heroicon::InformationCircle)
                                ->columns(3)
                                ->schema([
                                    TextInput::make('nombre')
                                        ->label('Nombre del Espacio')
                                        ->disabled()
                                        ->columnSpan(2),

                                    TextInput::make('codigo')
                                        ->label('Código Único')
                                        ->disabled()
                                        ->columnSpan(1),

                                    Select::make('tipo')
                                        ->label('Tipo de Espacio')
                                        ->options(TipoEspacio::options())
                                        ->disabled()
                                        ->columnSpan(1),

                                    Select::make('padre_id')
                                        ->label('Espacio Padre')
                                        ->relationship('padre', 'nombre')
                                        ->disabled()
                                        ->columnSpan(1),

                                    Select::make('ubicacion_id')
                                        ->label('Ubicación Física')
                                        ->relationship('ubicacion', 'nombre')
                                        ->disabled()
                                        ->columnSpan(1),

                                    Select::make('estado')
                                        ->label('Estado')
                                        ->options(EstadoEspacio::options())
                                        ->disabled()
                                        ->columnSpan(1),

                                    TextInput::make('capacidad_personas')
                                        ->label('Capacidad Máxima')
                                        ->disabled()
                                        ->suffix(' personas')
                                        ->columnSpan(1),

                                    TextInput::make('orden')
                                        ->label('Orden')
                                        ->disabled()
                                        ->columnSpan(1),
                                ]),

                            Tab::make('Configuración del Tipo')
                                ->icon(Heroicon::WrenchScrewdriver)
                                ->schema([

                                    // ─── Mesa ──────────────────────────
                                    Select::make('meta_datos.tipo_mesa')
                                        ->label('Forma / Tipo de la Mesa')
                                        ->options([
                                            'redonda' => 'Redonda',
                                            'cuadrada' => 'Cuadrada',
                                            'rectangular' => 'Rectangular',
                                            'barra' => 'Espacio de Barra / Taburete',
                                        ])
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::MESA->value),

                                    Select::make('meta_datos.zona_restaurante')
                                        ->label('Zona de Restaurante')
                                        ->options([
                                            'interior' => 'Salón Interior',
                                            'terraza' => 'Terraza / Aire Libre',
                                            'vip' => 'Zona Reservada / VIP',
                                            'barra' => 'Barra de Tragos',
                                        ])
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::MESA->value),

                                    // ─── Salón ─────────────────────────
                                    TextInput::make('meta_datos.metros_cuadrados')
                                        ->label('Metros Cuadrados (m²)')
                                        ->disabled()
                                        ->suffix('m²')
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::SALON->value),

                                    CheckboxList::make('meta_datos.equipamiento_incluido')
                                        ->label('Equipamiento Disponible')
                                        ->options([
                                            'proyector' => 'Proyector HD y Pantalla',
                                            'sonido' => 'Consola y Microfonía de Sonido',
                                            'clima' => 'Climatización Central / AC',
                                            'pizarra' => 'Pizarra Ejecutiva / Smartboard',
                                            'luces' => 'Iluminación Regulable para Eventos',
                                        ])
                                        ->disabled()
                                        ->columns(2)
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::SALON->value),

                                    // ─── Gimnasio ──────────────────────
                                    Textarea::make('meta_datos.restricciones_gimnasio')
                                        ->label('Políticas / Restricciones de Acceso')
                                        ->rows(3)
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::GYM->value),

                                    // ─── Restaurante ───────────────────
                                    Select::make('meta_datos.tipo_cocina')
                                        ->label('Tipo de Cocina')
                                        ->options([
                                            'buffet' => 'Buffet',
                                            'a_la_carta' => 'A la carta',
                                            'mixto' => 'Mixto (Buffet + Carta)',
                                            'barra' => 'Barra de tragos / Snacks',
                                        ])
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::RESTAURANTE->value),

                                    Select::make('meta_datos.tipo_servicio')
                                        ->label('Tipo de Servicio')
                                        ->options([
                                            'meseros' => 'Meseros',
                                            'autoservicio' => 'Autoservicio',
                                            'mixto' => 'Mixto',
                                        ])
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::RESTAURANTE->value),

                                    TextInput::make('meta_datos.horario_comida')
                                        ->label('Horario de Comida')
                                        ->placeholder('Ej. 07:00 - 22:00')
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::RESTAURANTE->value),

                                    TextInput::make('meta_datos.capacidad_mesas')
                                        ->label('Capacidad Total en Mesas')
                                        ->disabled()
                                        ->suffix(' personas')
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::RESTAURANTE->value),

                                    // ─── SPA ───────────────────────────
                                    Select::make('meta_datos.tipo_spa')
                                        ->label('Tipo de Servicio SPA')
                                        ->options([
                                            'masajes' => 'Masajes',
                                            'sauna' => 'Sauna',
                                            'hidroterapia' => 'Hidroterapia',
                                            'belleza' => 'Cabina de Belleza / Estética',
                                        ])
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::SPA->value),

                                    TextInput::make('meta_datos.capacidad_simultanea')
                                        ->label('Capacidad Simultánea')
                                        ->disabled()
                                        ->suffix(' personas')
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::SPA->value),

                                    CheckboxList::make('meta_datos.equipamiento_spa')
                                        ->label('Equipamiento Disponible')
                                        ->options([
                                            'camilla' => 'Camilla Profesional',
                                            'sauna' => 'Sauna Seca / Vapor',
                                            'jacuzzi' => 'Jacuzzi / Hidromasaje',
                                            'ducha_hidro' => 'Ducha de Hidroterapia',
                                            'vapor' => 'Baño de Vapor / Turco',
                                        ])
                                        ->disabled()
                                        ->columns(2)
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::SPA->value),

                                    // ─── Piscina ───────────────────────
                                    Select::make('meta_datos.tipo_piscina')
                                        ->label('Tipo de Piscina')
                                        ->options([
                                            'principal' => 'Piscina Principal',
                                            'ninos' => 'Piscina de Niños',
                                            'hidromasaje' => 'Hidromasaje / Jacuzzi',
                                            'mixta' => 'Mixta (Principal + Niños)',
                                        ])
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::PISCINA->value),

                                    TextInput::make('meta_datos.camastros')
                                        ->label('Cantidad de Camastros')
                                        ->disabled()
                                        ->suffix(' camastros')
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::PISCINA->value),

                                    TextInput::make('meta_datos.horario_operacion')
                                        ->label('Horario de Operación')
                                        ->placeholder('Ej. 06:00 - 22:00')
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::PISCINA->value),

                                    Toggle::make('meta_datos.toallas_incluidas')
                                        ->label('Toallas Incluidas')
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::PISCINA->value),

                                    // ─── Cancha Deportiva ──────────────
                                    Select::make('meta_datos.tipo_cancha')
                                        ->label('Tipo de Cancha')
                                        ->options([
                                            'tenis' => 'Tenis',
                                            'futbol' => 'Fútbol',
                                            'baloncesto' => 'Baloncesto',
                                            'voleibol' => 'Voleibol',
                                            'squash' => 'Squash',
                                            'multiusos' => 'Multiusos',
                                        ])
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::CANCHA->value),

                                    Select::make('meta_datos.superficie')
                                        ->label('Tipo de Superficie')
                                        ->options([
                                            'cesped' => 'Césped Natural / Sintético',
                                            'cemento' => 'Cemento / Hormigón',
                                            'arcilla' => 'Arcilla',
                                            'sintetico' => 'Sintético / Poliuretano',
                                        ])
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::CANCHA->value),

                                    Toggle::make('meta_datos.iluminacion_nocturna')
                                        ->label('Iluminación Nocturna')
                                        ->disabled()
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::CANCHA->value),
                                ]),

                            Tab::make('Descripción')
                                ->icon(Heroicon::DocumentText)
                                ->schema([
                                    RichEditor::make('descripcion')
                                        ->hiddenLabel()
                                        ->disabled()
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    ]),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}
