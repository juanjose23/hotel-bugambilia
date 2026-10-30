<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\Filament\Shared\Actions\VerGuiaAction;
use App\Filament\Shared\Forms\BuffetMenuForm;
use App\Interactors\Restaurante\Cocina\GestionarBuffet;
use App\Interactors\Restaurante\Cocina\OrganizarServicioBuffet;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Buffet;
use App\Repository\Models\Restaurante\BuffetPlato;
use App\Repository\Models\Shared\Stock;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Queries\Restaurante\Cocina\MenuBuffetCocinaQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class BuffetsCocina extends Page implements HasTable
{
    use HasPageShield, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cake';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante & Cocina';

    protected static ?string $navigationLabel = 'Buffets y eventos';

    protected static ?string $title = 'Buffets y eventos';

    protected static ?string $slug = 'restaurante/buffets';

    protected string $view = 'filament.pages.restaurante.buffets-cocina';

    public function table(Table $table): Table
    {
        return $table->query(Buffet::query()->with('moneda')->withExists('menu')->orderByDesc('fecha_servicio'))->columns([
            TextColumn::make('nombre')->searchable(), TextColumn::make('fecha_servicio')->label('Servicio')->date(),
            TextColumn::make('modalidad')->formatStateUsing(fn (string $state): string => match ($state) {
                'persona' => 'Por persona', 'evento' => 'Paquete de evento', 'mixto' => 'Paquete + adicionales', default => $state
            })->badge(), TextColumn::make('estado')->formatStateUsing(fn (string $state): string => $state === 'abierto' ? 'En servicio' : 'Cerrado')->badge()->color(fn (string $state): string => $state === 'abierto' ? 'success' : 'gray'),
            TextColumn::make('precio_persona')->label('Por persona')->formatStateUsing(fn (Buffet $record): string => ($record->moneda->codigo ?? '').' '.number_format((float) $record->precio_persona, 2))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('precio_paquete')->label('Paquete')->formatStateUsing(fn (Buffet $record): string => ($record->moneda->codigo ?? '').' '.number_format((float) $record->precio_paquete, 2))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('tipo_servicio')->label('Quién sirve')->formatStateUsing(fn (string $state): string => match ($state) {
                'autoservicio' => 'Se sirve la persona', 'mesero' => 'Sirve el mesero', 'mixto' => 'Servicio mixto', default => 'Por definir'
            })->badge(),
            TextColumn::make('comensales_previstos')->label('Previstos'), TextColumn::make('comensales_reales')->label('Atendidos'),
            TextColumn::make('cargado_at')->label('Cargo registrado')->dateTime()->placeholder('Pendiente'),
        ])->headerActions([
            Action::make('crear')->extraModalFooterActions([VerGuiaAction::make('buffet-crear')])->label('Crear buffet o evento')->modalWidth('5xl')->schema([
                TextInput::make('nombre')->extraAttributes(['data-guia' => 'buffet-nombre'])->required()->maxLength(150), DatePicker::make('fecha_servicio')->label('Fecha del servicio')->required()->default(today()),
                Select::make('reserva_id')->label('Reserva o evento relacionado (opcional)')->options(fn (): array => Reserva::query()->orderByDesc('id')->limit(100)->pluck('codigo_reserva', 'id')->all())->searchable(),
                Select::make('modalidad')->extraAttributes(['data-guia' => 'buffet-modalidad'])->options(['persona' => 'Por persona', 'evento' => 'Paquete de evento', 'mixto' => 'Paquete y personas adicionales'])->required()->default('mixto'),
                Select::make('moneda_id')->label('Moneda de los precios')->options(fn (): array => Moneda::query()->pluck('codigo', 'id')->all())->default(fn (): ?int => Moneda::query()->where('es_predeterminada', true)->first()?->id)->required(),
                TextInput::make('precio_persona')->label('Precio por persona adicional')->numeric()->minValue(0)->default(0)->required(),
                TextInput::make('precio_paquete')->label('Precio por paquete')->numeric()->minValue(0)->default(0)->required(),
                TextInput::make('comensales_previstos')->label('Comensales previstos')->numeric()->integer()->minValue(1)->required(),
                ...BuffetMenuForm::schema(),
            ])->action(function (array $data): void {
                /** @var array{nombre: string, fecha_servicio: string, modalidad: string, moneda_id: int, precio_persona: float, precio_paquete: float, comensales_previstos: int, reserva_id?: int|null, tipo_servicio: string, menu: array<array-key, array{plato_id: int|string, producto_variante_id: int|string, porciones_previstas: int|string}>} $data */
                /** @var array{tipo_servicio: string, menu: array<array-key, array{plato_id: int|string, producto_variante_id: int|string, porciones_previstas: int|string}>} $organizacion */
                $organizacion = ['tipo_servicio' => $data['tipo_servicio'], 'menu' => $data['menu']];
                unset($data['tipo_servicio'], $data['menu']);
                $this->operar(fn () => app(OrganizarServicioBuffet::class)->crear($data, $organizacion['tipo_servicio'], BuffetMenuForm::datos($organizacion['menu']), auth()->id() !== null ? (int) auth()->id() : null));
            }),
        ])->recordActions([
            Action::make('verMenu')->label('Ver menú y guía')->modalWidth('5xl')->icon('heroicon-o-book-open')->modalSubmitAction(false)->modalCancelActionLabel('Entendido')
                ->schema([ViewField::make('guia_menu')->view('filament.pages.restaurante.components.guia-menu-buffet')->viewData(fn (Buffet $record): array => ['menu' => app(MenuBuffetCocinaQuery::class)->detalle($record)])->dehydrated(false)]),
            Action::make('organizarMenu')->extraModalFooterActions([VerGuiaAction::make('buffet-menu')])->label('Organizar menú')->modalWidth('5xl')->visible(fn (Buffet $record): bool => $record->estado === 'abierto')
                ->fillForm(fn (Buffet $record): array => ['tipo_servicio' => $record->getAttribute('tipo_servicio'), 'menu' => $record->menu()->get()->map(fn (BuffetPlato $fila): array => ['plato_id' => $fila->plato_id, 'producto_variante_id' => $fila->producto_variante_id, 'porciones_previstas' => $fila->porciones_previstas])->all()])
                ->schema(BuffetMenuForm::schema())->action(function (Buffet $record, array $data): void {
                    /** @var array{tipo_servicio: string, menu: array<array-key, array{plato_id: int|string, producto_variante_id: int|string, porciones_previstas: int|string}>} $data */
                    $this->operar(fn () => app(OrganizarServicioBuffet::class)->configurar($record->id, $data['tipo_servicio'], BuffetMenuForm::datos($data['menu'])));
                }),

            Action::make('abastecer')->extraModalFooterActions([VerGuiaAction::make('buffet-abastecer')])->label('Enviar o reponer alimentos')->visible(fn (Buffet $record): bool => $record->estado === 'abierto' && $record->getAttribute('menu_exists') === true)
                ->schema([
                    Select::make('stock_id')->extraAttributes(['data-guia' => 'buffet-stock'])->label('Existencia de cocina / bar (cantidad y unidad de la variante)')->options(fn (Buffet $record): array => app(ExistenciasCocinaRepositorio::class)->disponibles()->when($record->menu()->exists(), fn (Builder $query) => $query->whereIn('producto_variante_id', $record->menu()->select('producto_variante_id')))->with(['variante.unidadMedida', 'variante.producto.unidadMedida', 'producto.unidadMedida', 'stockable'])->where('stockable_type', Ubicacion::class)->where('cantidad_actual', '>', 0)->get()->mapWithKeys(fn (Stock $stock): array => [$stock->id => ($stock->variante->producto->nombre ?? $stock->producto->nombre ?? 'Producto').' / '.($stock->variante->nombre_variante ?? '').' — '.$stock->cantidad_actual.' '.($stock->variante->unidadMedida->nombre ?? $stock->variante->producto->unidadMedida->nombre ?? $stock->producto->unidadMedida->nombre ?? 'unidades')])->all())->searchable()->required(),
                    TextInput::make('cantidad')->extraAttributes(['data-guia' => 'buffet-cantidad'])->label('Cantidad de producto terminado enviada')->helperText('Usa la unidad de la presentación seleccionada. Este envío no vuelve a consumir ingredientes de la receta.')->numeric()->minValue(0.0001)->required(),
                ])->action(function (Buffet $record, array $data): void {
                    /** @var array{stock_id: int, cantidad: float} $data */
                    $this->operar(fn () => app(GestionarBuffet::class)->abastecer($record->id, (int) $data['stock_id'], (float) $data['cantidad']));
                }),
            Action::make('cerrar')->extraModalFooterActions([VerGuiaAction::make('buffet-cerrar')])->label('Cerrar servicio')->modalHeading('Cierre del buffet: registra lo que quedó')->modalSubmitActionLabel('Confirmar cierre')->modalDescription('Registra las personas atendidas y mide lo que quedó. El cierre calcula lo servido y conserva los sobrantes separados.')->visible(fn (Buffet $record): bool => $record->estado === 'abierto')->requiresConfirmation()
                ->fillForm(fn (Buffet $record): array => ['comensales' => null, 'mediciones' => $record->existencias()->where('cantidad_actual', '>', 0)->with(['variante.producto', 'variante.unidadMedida'])->get()->map(fn (Stock $stock): array => ['stock_id' => $stock->id, 'producto' => ($stock->variante->producto->nombre ?? 'Producto').' / enviado: '.$stock->cantidad_actual.' '.($stock->variante->unidadMedida->nombre ?? 'unidades'), 'sobrante' => null, 'merma' => null])->all()])
                ->schema([
                    TextInput::make('comensales')->extraAttributes(['data-guia' => 'buffet-comensales'])->label('Personas realmente atendidas')->numeric()->integer()->minValue(0)->required(),
                    Repeater::make('mediciones')->extraAttributes(['data-guia' => 'buffet-mediciones'])->label('Mide el sobrante y el desperdicio de cada producto')->helperText('Escribe 0 cuando no haya sobrante o pérdida. Lo servido se calcula: enviado menos sobrante menos desperdicio.')->addable(false)->deletable(false)->reorderable(false)->schema([
                        Hidden::make('stock_id'), TextInput::make('producto')->disabled()->dehydrated(false),
                        TextInput::make('sobrante')->label('Sobrante pendiente de disposición')->numeric()->minValue(0)->required(),
                        TextInput::make('merma')->label('Desperdicio medido')->numeric()->minValue(0)->required(),
                    ]),
                ])->action(function (Buffet $record, array $data): void {
                    /** @var array{comensales: int, mediciones: list<array{stock_id: int, sobrante: float, merma: float}>} $data */
                    $mediciones = array_map(fn (array $m): array => ['stock_id' => (int) $m['stock_id'], 'sobrante' => (float) $m['sobrante'], 'merma' => (float) $m['merma']], $data['mediciones']);
                    $this->operar(fn () => app(GestionarBuffet::class)->cerrar($record->id, (int) $data['comensales'], $mediciones));
                }),
            Action::make('cargar')->extraModalFooterActions([VerGuiaAction::make('buffet-cargar')])->label('Agregar a una cuenta')->visible(fn (Buffet $record): bool => $record->cargado_at === null)
                ->schema([
                    Select::make('cuenta_id')->extraAttributes(['data-guia' => 'buffet-cuenta'])->label('Cuenta abierta de la misma moneda')->options(fn (Buffet $record): array => Cuenta::query()->where('estado', 2)->where('moneda_id', $record->moneda_id)->pluck('numero_cuenta', 'id')->all())->searchable()->required(),
                    TextInput::make('personas')->extraAttributes(['data-guia' => 'buffet-personas'])->label('Personas por cobrar fuera del paquete')->helperText('No incluya de nuevo a las personas cubiertas por el paquete.')->numeric()->integer()->minValue(0)->default(0)->required(),
                    TextInput::make('paquetes')->extraAttributes(['data-guia' => 'buffet-paquetes'])->label('Cantidad de paquetes')->numeric()->integer()->minValue(0)->default(0)->required(),
                ])->action(function (Buffet $record, array $data): void {
                    /** @var array{cuenta_id: int, personas: int, paquetes: int} $data */
                    $this->operar(fn () => app(GestionarBuffet::class)->cargarCuenta($record->id, (int) $data['cuenta_id'], (int) $data['personas'], (int) $data['paquetes'], auth()->id() !== null ? (int) auth()->id() : null));
                }),
            Action::make('descartar')->label('Descartar sobrantes')->color('danger')->visible(fn (Buffet $record): bool => $record->estado === 'cerrado')->requiresConfirmation()
                ->schema([Textarea::make('motivo')->required()->label('Motivo de disposición')])
                ->action(function (Buffet $record, array $data): void {
                    /** @var array{motivo: string} $data */
                    $this->operar(fn () => app(GestionarBuffet::class)->descartarSobrantes($record->id, $data['motivo']));
                }),
        ]);
    }

    private function operar(\Closure $accion): void
    {
        try {
            $accion();
            Notification::make()->title('Operación registrada')->success()->send();
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
