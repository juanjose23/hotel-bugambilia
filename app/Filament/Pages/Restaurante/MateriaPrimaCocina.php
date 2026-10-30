<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\Restaurante\UbicacionCocina;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use App\Filament\Shared\Forms\ProductoSelect;
use App\Filament\Shared\Forms\ProductoVarianteSelect;
use App\Interactors\Restaurante\Cocina\TransformarMateriaPrimaCocina;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Restaurante\TransformacionMateriaPrima;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Cocina\ControlOperacionCocinaQuery;
use App\Repository\Queries\Restaurante\Cocina\ObtenerHistorialTransformacionesCocina;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use UnitEnum;

/**
 * @property Schema $form
 */
final class MateriaPrimaCocina extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante & Cocina';

    protected static ?string $navigationLabel = 'Preparar insumos';

    protected static ?string $title = 'Preparar y porcionar insumos';

    protected static ?string $slug = 'restaurante/materia-prima-cocina';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.restaurante.materia-prima-cocina';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'ubicacion_origen_id' => self::ubicacionCocinaId(),
            'cantidad_procesada' => 1,
            'items' => [['ubicacion_destino_id' => self::ubicacionCocinaId()]],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $consulta = app(ControlOperacionCocinaQuery::class);

        return $schema->schema([
            Tabs::make('operacion_insumos')->tabs([
                Tab::make('Preparar insumos')->icon('heroicon-o-scissors')->schema([
                    Wizard::make([
                        Step::make('Elegir insumo')->description('Qué retirarás y de dónde')->icon('heroicon-o-archive-box')->schema([
                            Section::make('¿Qué vas a preparar?')->description('Empieza por la bodega. Solo aparecen insumos disponibles y en buen estado.')->columns(['default' => 1, 'md' => 2])->schema([
                                Select::make('ubicacion_origen_id')->label('Retirar de')->options(fn (): array => $consulta->ubicaciones())->native(false)->searchable()->required()->live()
                                    ->afterStateUpdated(function (Set $set, mixed $state): void {
                                        $set('producto_origen_id', null);
                                        $set('variante_origen_id', null);
                                        $set('items', [['ubicacion_destino_id' => is_numeric($state) ? (int) $state : null]]);
                                    }),
                                Select::make('producto_origen_id')->label('Insumo que vas a usar')->options(fn (Get $get): array => $consulta->productos(self::idOpcional($get('ubicacion_origen_id'))))->native(false)->searchable()->required()->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('variante_origen_id', null)),
                                Select::make('variante_origen_id')->label('Presentación disponible')->options(fn (Get $get): array => $consulta->variantes(self::idOpcional($get('producto_origen_id')), self::idOpcional($get('ubicacion_origen_id'))))->native(false)->searchable()->required()->live(),
                                TextInput::make('cantidad_procesada')->label('Cantidad que vas a retirar')->numeric()->minValue(.001)->required()->live(onBlur: true)
                                    ->maxValue(fn (Get $get): float => $consulta->seleccion(self::idOpcional($get('variante_origen_id')), self::idOpcional($get('ubicacion_origen_id')))['cantidad'])
                                    ->helperText(fn (Get $get): string => 'Escribe la cantidad en '.$consulta->seleccion(self::idOpcional($get('variante_origen_id')), null)['unidad'].'.'),
                            ]),
                        ]),
                        Step::make('Registrar resultado')->description('Lo útil y lo que se perdió')->icon('heroicon-o-beaker')->schema([
                            Section::make('¿Qué obtuviste?')->description('Agrega cada corte, preparación o bebida obtenida. Si el rendimiento es menor, la diferencia se registra como pérdida.')->schema([
                                Repeater::make('items')->label('Resultados de la preparación')->minItems(1)->defaultItems(1)->reorderable(false)->addActionLabel('Agregar otro resultado')->collapsible()
                                    ->itemLabel(fn (array $state): string => ! empty($state['es_merma']) ? 'Pérdida registrada' : 'Producto obtenido')
                                    ->schema([
                                        Grid::make(['default' => 1, 'md' => 2])->schema([
                                            ProductoSelect::make('producto_destino_id')->label('Producto que obtuviste')->live()->required()->afterStateUpdated(fn (Set $set) => $set('variante_destino_id', null)),
                                            ProductoVarianteSelect::make('variante_destino_id', 'producto_destino_id')->label('Presentación obtenida')->live()->required(fn (Get $get): bool => ! (bool) $get('es_merma'))->hidden(fn (Get $get): bool => (bool) $get('es_merma')),
                                            TextInput::make('cantidad')->label('Cantidad realmente obtenida')->numeric()->minValue(.001)->required()->live(onBlur: true)
                                                ->helperText(fn (Get $get): string => 'Cantidad en '.$consulta->seleccion(self::idOpcional($get((bool) $get('es_merma') ? '../../variante_origen_id' : 'variante_destino_id')), null)['unidad'].'.'),
                                            Select::make('ubicacion_destino_id')->label('Guardar en')->options(fn (): array => $consulta->ubicaciones())->default(fn (Get $get): mixed => $get('../../ubicacion_origen_id'))->searchable()->required(fn (Get $get): bool => ! (bool) $get('es_merma'))->hidden(fn (Get $get): bool => (bool) $get('es_merma')),
                                            Toggle::make('es_merma')->label('Este resultado es una pérdida')->helperText('Úsalo solo para registrar desperdicio, no para productos que vas a conservar.')->live()->columnSpanFull()
                                                ->afterStateUpdated(function (Get $get, Set $set, mixed $state): void {
                                                    if ($state) {
                                                        $set('producto_destino_id', $get('../../producto_origen_id'));
                                                        $set('variante_destino_id', null);
                                                        $set('ubicacion_destino_id', $get('../../ubicacion_origen_id'));
                                                    }
                                                }),
                                            TextInput::make('observaciones')->label('Detalle opcional')->placeholder('Ej.: cortes para el almuerzo')->columnSpanFull(),
                                        ]),
                                    ]),
                                DatePicker::make('fecha_vencimiento')->label('Usar hasta (opcional)')->minDate(today())->helperText('Puedes indicar una fecha menor que la del insumo original.'),
                                Textarea::make('observaciones')->label('Nota para el equipo (opcional)')->rows(2),
                            ]),
                        ]),
                        Step::make('Revisar y guardar')->description('Confirma las cantidades')->icon('heroicon-o-check-circle')->schema([
                            ViewField::make('resumen_operacion')->view('filament.pages.restaurante.components.resumen-preparacion')->dehydrated(false),
                        ]),
                    ])->nextAction(fn (Action $action): Action => $action->label('Continuar'))->previousAction(fn (Action $action): Action => $action->label('Volver'))
                        ->submitAction(new HtmlString(Blade::render('<x-filament::button type="submit" icon="heroicon-o-check-circle" wire:loading.attr="disabled" wire:target="guardar">Guardar preparación</x-filament::button>'))),
                ]),
                Tab::make('Existencias')->icon('heroicon-o-archive-box')->schema([ViewField::make('stock_cocina_tabla')->view('filament.pages.restaurante.components.stock-cocina-tabla')->dehydrated(false)]),
                Tab::make('Movimientos recientes')->icon('heroicon-o-clock')->schema([ViewField::make('historial_transformaciones_tabla')->view('filament.pages.restaurante.components.historial-transformaciones-tabla')->dehydrated(false)]),
            ]),
        ])->statePath('data');
    }

    private static function idOpcional(mixed $valor): ?int
    {
        return is_numeric($valor) ? (int) $valor : null;
    }

    /** @return array{origen: array{cantidad: float, unidad: string, nombre: string, ubicacion: string}, retiro: float, resultados: list<array{nombre: string, cantidad: float, unidad: string, bodega: string, merma: bool}>} */
    public function resumenPreparacion(): array
    {
        $consulta = app(ControlOperacionCocinaQuery::class);
        $data = $this->data ?? [];
        $resultados = [];
        $ubicaciones = $consulta->ubicaciones();
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $seleccion = $consulta->seleccion(self::idOpcional(! empty($item['es_merma']) ? ($data['variante_origen_id'] ?? null) : ($item['variante_destino_id'] ?? null)), null);
            $resultados[] = ['nombre' => $seleccion['nombre'], 'cantidad' => is_numeric($item['cantidad'] ?? null) ? (float) $item['cantidad'] : 0.0, 'unidad' => $seleccion['unidad'], 'bodega' => $ubicaciones[self::idOpcional($item['ubicacion_destino_id'] ?? null) ?? 0] ?? 'Por indicar', 'merma' => (bool) ($item['es_merma'] ?? false)];
        }

        return ['origen' => $consulta->seleccion(self::idOpcional($data['variante_origen_id'] ?? null), self::idOpcional($data['ubicacion_origen_id'] ?? null)), 'retiro' => is_numeric($data['cantidad_procesada'] ?? null) ? (float) $data['cantidad_procesada'] : 0.0, 'resultados' => $resultados];
    }

    public function guardar(): void
    {
        try {
            $usuarioId = Auth::id() !== null ? (int) Auth::id() : null;
            $transformacion = app(TransformarMateriaPrimaCocina::class)->ejecutar($this->form->getState(), $usuarioId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo transformar')->body($exception->getMessage())->danger()->send();

            return;
        }

        Notification::make()
            ->title('Preparación registrada')
            ->body("Transformación {$transformacion->codigo} registrada en inventario.")
            ->success()
            ->send();

        session()->flash('cocina_preparacion_guardada', $transformacion->codigo);
        $this->redirect(self::getUrl(), navigate: true);
    }

    public function table(Table $table): Table
    {
        $cocinaId = self::ubicacionCocinaId() ?? 0;

        return $table
            ->query(
                Stock::query()
                    ->where('stockable_type', Ubicacion::class)
                    ->where('stockable_id', $cocinaId)
                    ->where('cantidad_actual', '>', 0)
                    ->whereHas('variante.producto.categoria', function (Builder $cat): void {
                        $cat->whereNotIn('nombre', [
                            'Amenities',
                            'Aseo y Limpieza',
                            'Limpieza',
                            'Lencería',
                            'Mantenimiento',
                            'Equipamiento',
                            'Oficina',
                            'Ferretería',
                            'Herramientas',
                        ]);
                    })
                    ->with(['variante.producto.unidadMedida', 'variante.unidadMedida', 'lote'])
            )
            ->columns([
                TextColumn::make('variante.producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('variante.nombre_variante')
                    ->label('Variante / Presentación')
                    ->badge()
                    ->searchable(),

                TextColumn::make('cantidad_actual')
                    ->label('Cantidad Disponible')
                    ->numeric(decimalPlaces: 3)
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('unidad_medida')
                    ->label('Unidad')
                    ->state(fn (Stock $record): string => $record->variante->unidadMedida->nombre ?? $record->variante->producto->unidadMedida->nombre ?? 'unid'),

                MontoMonedaColumn::make('costo_unitario')
                    ->label('Costo Unitario')
                    ->state(fn (Stock $record): float => (float) ($record->lote->costo_unitario ?? 0.0))
                    ->alignEnd(),

                MontoMonedaColumn::make('valor_total')
                    ->label('Valor Total Estimado')
                    ->state(fn (Stock $record): float => (float) $record->cantidad_actual * (float) ($record->lote->costo_unitario ?? 0.0))
                    ->alignEnd(),
            ])
            ->defaultSort('id', 'desc');
    }

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('volverCocina')
                ->label('Volver a Cocina')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => CocinaPedidos::getUrl()),
            Action::make('conciliacionRecetas')
                ->label('Conciliar Recetas')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray')
                ->url(fn (): string => ConciliacionRecetasCocina::getUrl()),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    private static function ubicacionCocinaId(): ?int
    {
        $id = Ubicacion::query()
            ->where('nombre', UbicacionCocina::RESTAURANTE->value)
            ->value('id');

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function obtenerStockCocinaListado(): Collection
    {
        $cocinaId = self::idOpcional($this->data['ubicacion_origen_id'] ?? null) ?? self::ubicacionCocinaId();

        if ($cocinaId === null) {
            return new Collection;
        }

        /** @var Collection<int, Stock> $stocks */
        $stocks = Stock::query()
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', $cocinaId)
            ->where('cantidad_actual', '>', 0)
            ->with(['variante.producto.unidadMedida', 'variante.unidadMedida', 'lote'])
            ->orderBy('id', 'desc')
            ->get();

        return $stocks;
    }

    /**
     * @return Collection<int, TransformacionMateriaPrima>
     */
    public function obtenerHistorialTransformacionesListado(): Collection
    {
        return app(ObtenerHistorialTransformacionesCocina::class)->obtenerRecientes(20);
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user !== null && ($user->can('Page:MateriaPrimaCocina') || $user->can('Page:CocinaPedidos'));
    }
}
