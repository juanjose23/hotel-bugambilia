<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers;

use App\Enums\Activos\EstadoActivo;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Espacios\GenerarCodigoSubEspacio;
use App\Interactors\Espacios\SincronizarActivosEspacio;
use App\Interactors\Espacios\ValidarCapacidadMesas;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Queries\Espacios\ConsultarCapacidadMesas;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('sub_espacio_form_tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Datos Principales')
                            ->icon(Heroicon::InformationCircle)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('nombre')
                                        ->label('Nombre del Sub-espacio')
                                        ->placeholder('Ej. Mesa 1, Terraza A, Barra 1')
                                        ->prefixIcon(Heroicon::Tag)
                                        ->required()
                                        ->maxLength(150)
                                        ->columnSpan(1),

                                    Select::make('tipo')
                                        ->label('Tipo de Sub-espacio')
                                        ->placeholder('Seleccione tipo')
                                        ->options(TipoEspacio::options())
                                        ->default(TipoEspacio::MESA->value)
                                        ->required()
                                        ->live()
                                        ->native(false)
                                        ->prefixIcon(Heroicon::Squares2x2)
                                        ->columnSpan(1),

                                    TextInput::make('codigo')
                                        ->label('Código Identificador')
                                        ->placeholder('Ej. MESA-0001 (Opcional)')
                                        ->prefixIcon(Heroicon::Hashtag)
                                        ->maxLength(50)
                                        ->unique(table: 'espacios', column: 'codigo', ignoreRecord: true)
                                        ->helperText('Si se deja vacío, se generará automáticamente según el tipo.')
                                        ->columnSpan(1),

                                    Select::make('estado')
                                        ->label('Estado Operativo')
                                        ->options(EstadoEspacio::class)
                                        ->default(EstadoEspacio::Disponible->value)
                                        ->required()
                                        ->native(false)
                                        ->prefixIcon(Heroicon::ArrowPath)
                                        ->columnSpan(1),

                                    TextInput::make('capacidad_personas')
                                        ->label('Capacidad (Personas)')
                                        ->placeholder('Ej. 4')
                                        ->prefixIcon(Heroicon::Users)
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(4)
                                        ->columnSpan(1),

                                    TextInput::make('orden')
                                        ->label('Orden en Mapa / Listado')
                                        ->placeholder('0')
                                        ->prefixIcon(Heroicon::ArrowDownCircle)
                                        ->required()
                                        ->numeric()
                                        ->default(0)
                                        ->columnSpan(1),

                                    Toggle::make('web')
                                        ->label('Visible en Sitio Web')
                                        ->default(true),

                                    Toggle::make('reservable')
                                        ->label('Permite Reservaciones Directas')
                                        ->default(true),
                                ]),
                            ]),

                        Tab::make('Mobiliario / Activos')
                            ->icon(Heroicon::ArchiveBox)
                            ->schema([
                                Select::make('activos_ids')
                                    ->label(fn ($get) => $get('tipo') === TipoEspacio::MESA->value ? 'Mobiliario Físico (Mesa y Sillas)' : 'Activos Físicos Asignados (Mobiliario, Equipos)')
                                    ->placeholder('Buscar y seleccionar activos físicos...')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->options(function (?Espacio $record): array {
                                        $recordKey = $record?->getKey();
                                        $recordId = is_numeric($recordKey) ? (int) $recordKey : null;

                                        return Activo::query()
                                            ->select(['id', 'codigo_inventario', 'nombre_descriptivo'])
                                            ->where('estado', EstadoActivo::Activo->value)
                                            ->where(function (Builder $q) use ($recordId) {
                                                $q->whereDoesntHave('asignaciones', fn (Builder $sub) => $sub->whereNull('fecha_fin'));

                                                if ($recordId !== null) {
                                                    $q->orWhereHas('asignaciones', fn (Builder $sub) => $sub
                                                        ->whereNull('fecha_fin')
                                                        ->where('asignable_type', Espacio::class)
                                                        ->where('asignable_id', $recordId)
                                                    );
                                                }
                                            })
                                            ->get()
                                            ->mapWithKeys(fn (Activo $activo): array => [
                                                $activo->id => sprintf(
                                                    '%s — %s',
                                                    $activo->codigo_inventario,
                                                    $activo->nombre_descriptivo
                                                ),
                                            ])
                                            ->all();
                                    })
                                    ->helperText(fn ($get) => $get('tipo') === TipoEspacio::MESA->value
                                        ? 'Para mesas de restaurante, seleccione el activo físico de la mesa y sus sillas. La mesa requiere un activo físico para operar en comandas.'
                                        : 'Para zonas o ambientes (ej. Terraza, Bar), puede asignar activos generales como barras o sofás. Las mesas individuales pertenecientes a esta zona se gestionan dentro de ella.'
                                    )
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Configuración del Tipo')
                            ->icon(Heroicon::WrenchScrewdriver)
                            ->visible(fn ($get) => in_array($get('tipo'), [
                                TipoEspacio::MESA->value,
                                TipoEspacio::AMBIENTE->value,
                                TipoEspacio::TERRAZA->value,
                                TipoEspacio::BAR->value,
                                TipoEspacio::SALON->value,
                            ]))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('meta_datos.tipo_mesa')
                                            ->label('Forma de la Mesa')
                                            ->options([
                                                'cuadrada' => 'Cuadrada',
                                                'redonda' => 'Redonda',
                                                'rectangular' => 'Rectangular',
                                                'barra' => 'Barra / Taburete',
                                            ])
                                            ->default('cuadrada')
                                            ->native(false)
                                            ->prefixIcon(Heroicon::TableCells)
                                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::MESA->value),

                                        Toggle::make('meta_datos.permite_union')
                                            ->label('Permite Unión con otras Mesas')
                                            ->default(true)
                                            ->inline(false)
                                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::MESA->value),

                                        TextInput::make('meta_datos.metros_cuadrados')
                                            ->label('Metros Cuadrados (m²)')
                                            ->placeholder('Ej. 80')
                                            ->numeric()
                                            ->suffix('m²')
                                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::SALON->value),

                                        CheckboxList::make('meta_datos.caracteristicas')
                                            ->label('Características del Ambiente')
                                            ->options([
                                                'Aire Acondicionado' => 'Aire Acondicionado',
                                                'Vista al Jardín' => 'Vista al Jardín',
                                                'Pérgola Iluminada' => 'Pérgola Iluminada',
                                                'Música de Fondo' => 'Música de Fondo',
                                                'Barra de Cocteles' => 'Barra de Cocteles',
                                                'Garzón Dedicado' => 'Garzón Dedicado / Servicio VIP',
                                                'Asientos Lounge' => 'Asientos Lounge',
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->visible(fn ($get) => in_array($get('tipo'), [
                                                TipoEspacio::AMBIENTE->value,
                                                TipoEspacio::TERRAZA->value,
                                                TipoEspacio::BAR->value,
                                            ])),

                                        CheckboxList::make('meta_datos.equipamiento_incluido')
                                            ->label('Equipamiento Disponible')
                                            ->options([
                                                'proyector' => 'Proyector HD y Pantalla',
                                                'sonido' => 'Consola y Microfonía de Sonido',
                                                'clima' => 'Climatización Central / AC',
                                                'pizarra' => 'Pizarra Ejecutiva / Smartboard',
                                                'luces' => 'Iluminación Regulable para Eventos',
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->visible(fn ($get) => $get('tipo') === TipoEspacio::SALON->value),
                                    ]),
                            ]),

                        Tab::make('Descripción')
                            ->icon(Heroicon::DocumentText)
                            ->schema([
                                Textarea::make('descripcion')
                                    ->label('Notas / Especificaciones')
                                    ->placeholder('Detalles adicionales sobre este sub-espacio...')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['padre', 'ubicacion', 'inventarioFijo.activo', 'hijos']))
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

                TextColumn::make('sub_espacios_hijos')
                    ->label('Sub-mesas / Hijos')
                    ->state(function (Espacio $record): string {
                        if ($record->tipo === TipoEspacio::MESA) {
                            return '-';
                        }
                        $conteo = $record->hijos->count();

                        return $conteo > 0 ? "{$conteo} elementos" : '0';
                    })
                    ->badge(fn (Espacio $record): bool => $record->tipo !== TipoEspacio::MESA)
                    ->color(fn (Espacio $record): string => $record->hijos->isNotEmpty() ? 'info' : 'gray')
                    ->alignCenter(),

                TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('activo_asignado')
                    ->label('Mobiliario / Activos')
                    ->state(function (Espacio $record): string {
                        $asignaciones = $record->inventarioFijo;
                        $count = $asignaciones->count();

                        if ($count === 0) {
                            return $record->tipo === TipoEspacio::MESA ? 'Sin Mobiliario Asignado' : '-';
                        }

                        $primerActivo = $asignaciones->first()?->activo;
                        $primerNombre = $primerActivo
                            ? sprintf('%s (%s)', $primerActivo->codigo_inventario, $primerActivo->nombre_descriptivo)
                            : '1 Activo';

                        return $count > 1 ? sprintf('%s (+%d)', $primerNombre, $count - 1) : $primerNombre;
                    })
                    ->tooltip(function (Espacio $record): ?string {
                        $asignaciones = $record->inventarioFijo;
                        if ($asignaciones->isEmpty()) {
                            return null;
                        }

                        return $asignaciones
                            ->map(fn ($asig) => $asig->activo ? sprintf('%s: %s', $asig->activo->codigo_inventario, $asig->activo->nombre_descriptivo) : '')
                            ->filter()
                            ->implode("\n");
                    })
                    ->badge(fn (Espacio $record): bool => $record->tipo === TipoEspacio::MESA)
                    ->color(fn (Espacio $record): string => $record->inventarioFijo->isNotEmpty() ? 'success' : ($record->tipo === TipoEspacio::MESA ? 'danger' : 'gray'))
                    ->icon(fn (Espacio $record) => $record->inventarioFijo->isNotEmpty() ? Heroicon::CheckCircle : ($record->tipo === TipoEspacio::MESA ? Heroicon::ExclamationCircle : null)),

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
                    ->label('Nuevo Sub-espacio')
                    ->icon(Heroicon::Plus)
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->before(function (CreateAction $action, ValidarCapacidadMesas $validarCapacidadMesas, ConsultarCapacidadMesas $consultarCapacidadMesas) {
                        /** @var Espacio $padre */
                        $padre = $this->getOwnerRecord();

                        $padreKey = $padre->getKey();
                        $padreId = is_numeric($padreKey) ? (int) $padreKey : 0;
                        $grandKey = $padre->padre?->getKey();
                        $grandId = is_numeric($grandKey) ? (int) $grandKey : null;

                        $restauranteId = $padre->tipo === TipoEspacio::RESTAURANTE
                            ? $padreId
                            : ($padre->padre?->tipo === TipoEspacio::RESTAURANTE ? $grandId : null);

                        if ($restauranteId === null) {
                            return;
                        }

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
                        /** @var Espacio $padre */
                        $padre = $this->getOwnerRecord();
                        $data['padre_id'] = $padre->getKey();
                        $data['ubicacion_id'] = $padre->ubicacion_id;

                        if (empty($data['codigo'])) {
                            $tipo = isset($data['tipo'])
                                ? (is_string($data['tipo']) ? TipoEspacio::from($data['tipo']) : $data['tipo'])
                                : TipoEspacio::OTRO;

                            $data['codigo'] = $generarCodigoSubEspacio->execute($tipo);
                        }

                        return $data;
                    })
                    ->after(function (Espacio $record, array $data, SincronizarActivosEspacio $sincronizarActivosEspacio): void {
                        /** @var array<int|string> $activosIds */
                        $activosIds = is_array($data['activos_ids'] ?? null) ? $data['activos_ids'] : [];
                        $userId = (int) (auth()->id() ?? 1);

                        $sincronizarActivosEspacio->ejecutar(
                            espacio: $record,
                            activosIds: $activosIds,
                            userId: $userId,
                            motivo: "Asignación inicial al crear sub-espacio {$record->nombre}"
                        );
                    }),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('Ver detalles')
                    ->icon(Heroicon::Eye)
                    ->iconButton()
                    ->modalHeading(fn (Espacio $record): string => "Detalles: {$record->nombre}")
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->fillForm(function (Espacio $record): array {
                        $data = $record->toArray();
                        $data['activos_list'] = $record->inventarioFijo
                            ->map(fn ($asig) => $asig->activo ? sprintf('%s — %s', $asig->activo->codigo_inventario, $asig->activo->nombre_descriptivo) : null)
                            ->filter()
                            ->values()
                            ->all();

                        return $data;
                    })
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

                            Tab::make('Mobiliario Asignado')
                                ->icon(Heroicon::ArchiveBox)
                                ->schema([
                                    CheckboxList::make('activos_list')
                                        ->label('Activos y Mobiliario Fijo')
                                        ->options(function (Espacio $record): array {
                                            return $record->inventarioFijo
                                                ->mapWithKeys(fn ($asig) => $asig->activo ? [
                                                    sprintf('%s — %s', $asig->activo->codigo_inventario, $asig->activo->nombre_descriptivo) => sprintf('%s — %s (N/S: %s)', $asig->activo->codigo_inventario, $asig->activo->nombre_descriptivo, $asig->activo->numero_serie ?? 'S/N'),
                                                ] : [])
                                                ->all();
                                        })
                                        ->disabled()
                                        ->columns(1)
                                        ->columnSpanFull(),
                                ]),

                            Tab::make('Configuración')
                                ->icon(Heroicon::WrenchScrewdriver)
                                ->schema([
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

                                    TextInput::make('meta_datos.metros_cuadrados')
                                        ->label('Metros Cuadrados (m²)')
                                        ->disabled()
                                        ->suffix('m²')
                                        ->visible(fn ($get) => $get('tipo') === TipoEspacio::SALON->value),
                                ]),

                            Tab::make('Descripción')
                                ->icon(Heroicon::DocumentText)
                                ->schema([
                                    Textarea::make('descripcion')
                                        ->label('Descripción')
                                        ->disabled()
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    ]),

                Action::make('gestionar_sub_espacios')
                    ->label('Administrar Mesas')
                    ->icon(Heroicon::ArrowTopRightOnSquare)
                    ->iconButton()
                    ->color('primary')
                    ->tooltip(fn (Espacio $record): string => "Abrir \"{$record->nombre}\" para administrar sus mesas y sub-espacios")
                    ->url(fn (Espacio $record): string => route('filament.admin.resources.habitaciones.espacios.edit', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Espacio $record): bool => $record->tipo !== TipoEspacio::MESA),

                EditAction::make()
                    ->iconButton()
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading(fn (Espacio $record): string => "Editar: {$record->nombre}")
                    ->fillForm(function (Espacio $record): array {
                        $data = $record->attributesToArray();
                        $data['activos_ids'] = $record->inventarioFijo->pluck('activo_id')->all();

                        return $data;
                    })
                    ->after(function (Espacio $record, array $data, SincronizarActivosEspacio $sincronizarActivosEspacio): void {
                        /** @var array<int|string> $activosIds */
                        $activosIds = is_array($data['activos_ids'] ?? null) ? $data['activos_ids'] : [];
                        $userId = (int) (auth()->id() ?? 1);

                        $sincronizarActivosEspacio->ejecutar(
                            espacio: $record,
                            activosIds: $activosIds,
                            userId: $userId,
                            motivo: "Actualización de mobiliario en sub-espacio {$record->nombre}"
                        );
                    }),

                DeleteAction::make()->iconButton(),
            ]);
    }
}
