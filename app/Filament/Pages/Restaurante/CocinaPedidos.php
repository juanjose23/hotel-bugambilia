<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Filament\Resources\Restaurante\ProcesoCocinaResource\ProcesoCocinaResource;
use App\Filament\Shared\Actions\Restaurante\MermaGlobalDiariaAction;
use App\Filament\Shared\Actions\Restaurante\SolicitudAbastecimientoCocinaAction;
use App\Interactors\Restaurante\Cocina\AnularItemPedido;
use App\Interactors\Restaurante\Cocina\AutorizarSustitucionIngrediente;
use App\Interactors\Restaurante\Cocina\MarcarItemPedidoListo;
use App\Interactors\Restaurante\Cocina\MarcarItemServido;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Cocina\AnalizarFaltantesPedidoCocina;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidosCocinaQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\CarbonInterface;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @property Schema $sustitucionForm
 */
final class CocinaPedidos extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-fire';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Cocina';

    protected static ?string $title = 'Centro de Cocina';

    protected static ?string $slug = 'restaurante/cocina';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.resources.restaurante.cocina-pedidos';

    /** @var Collection<int, Pedido> */
    public Collection $pedidos;

    public ?string $areaSeleccionada = null;

    public int $ultimoTotalPedidos = 0;

    public ?int $pedidoConFaltantesId = null;

    /** @var list<array<string, mixed>> */
    public array $faltantesPreparacion = [];

    /** @var array<int, int|string|null> */
    public array $sustitutosSeleccionados = [];

    /** @var array<string, mixed>|null */
    public ?array $sustitucionData = [];

    private ObtenerPedidosCocinaQuery $pedidosQuery;

    private MarcarItemPedidoListo $marcarItemListo;

    private MarcarItemServido $marcarItemServido;

    private AnularItemPedido $anularItemPedido;

    private EnviarPedidoACocina $enviarACocina;

    private AnalizarFaltantesPedidoCocina $analizarFaltantes;

    private AutorizarSustitucionIngrediente $autorizarSustitucion;

    public function boot(
        ObtenerPedidosCocinaQuery $pedidosQuery,
        MarcarItemPedidoListo $marcarItemListo,
        MarcarItemServido $marcarItemServido,
        AnularItemPedido $anularItemPedido,
        EnviarPedidoACocina $enviarACocina,
        AnalizarFaltantesPedidoCocina $analizarFaltantes,
        AutorizarSustitucionIngrediente $autorizarSustitucion,
    ): void {
        $this->pedidosQuery = $pedidosQuery;
        $this->marcarItemListo = $marcarItemListo;
        $this->marcarItemServido = $marcarItemServido;
        $this->anularItemPedido = $anularItemPedido;
        $this->enviarACocina = $enviarACocina;
        $this->analizarFaltantes = $analizarFaltantes;
        $this->autorizarSustitucion = $autorizarSustitucion;
    }

    public function mount(): void
    {
        $this->areaSeleccionada = $this->normalizarAreaCocina(request()->query('area'));
        $this->cargarPedidos();
        $this->sustitucionForm->fill();
    }

    public function hydrate(): void
    {
        $this->cargarPedidos();
    }

    protected function getViewData(): array
    {
        $this->cargarPedidos();

        return [
            'pedidos' => $this->pedidos,
        ];
    }

    public function cargarPedidos(): void
    {
        $this->areaSeleccionada = $this->normalizarAreaCocina($this->areaSeleccionada);
        $this->pedidos = $this->pedidosQuery->ejecutar($this->areaSeleccionada);

        $nuevoTotal = $this->pedidos->count();
        if ($nuevoTotal > $this->ultimoTotalPedidos && $this->ultimoTotalPedidos > 0) {
            $this->dispatch('nuevo-pedido-audio');
        }
        $this->ultimoTotalPedidos = $nuevoTotal;
    }

    public function marcarItemListo(int $itemId): void
    {
        try {
            $item = $this->marcarItemListo->ejecutar($itemId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo completar el plato')->body($exception->getMessage())->danger()->send();

            return;
        }

        if ($item !== null) {
            Notification::make()->title("Platillo listo: {$item->plato?->nombre}")->success()->send();
        }

        $this->cargarPedidos();
    }

    public function marcarItemServido(int $itemId): void
    {
        try {
            $item = $this->marcarItemServido->ejecutar($itemId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo marcar como servido')->body($exception->getMessage())->danger()->send();

            return;
        }

        if ($item !== null) {
            Notification::make()->title("Platillo servido: {$item->plato?->nombre}")->success()->send();
        }

        $this->cargarPedidos();
    }

    public function anularItemPedido(int $itemId): void
    {
        try {
            $item = $this->anularItemPedido->ejecutar($itemId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo anular el plato')->body($exception->getMessage())->danger()->send();

            return;
        }

        if ($item !== null) {
            Notification::make()->title("Platillo anulado: {$item->plato?->nombre}")->warning()->send();
        }

        $this->cargarPedidos();
    }

    public function prepararAlimento(int $pedidoId): void
    {
        try {
            $pedidoParaAnalisis = Pedido::query()->with(['items.plato.receta'])->find($pedidoId);
            if ($pedidoParaAnalisis instanceof Pedido) {
                $faltantes = $this->analizarFaltantes->ejecutar($pedidoParaAnalisis);

                if ($faltantes !== []) {
                    $this->abrirSustitucionRapida($pedidoId, $faltantes);

                    return;
                }
            }

            $pedido = $this->enviarACocina->ejecutarPorId($pedidoId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo iniciar preparación')->body($exception->getMessage())->danger()->send();

            return;
        }

        Notification::make()->title('Preparación iniciada')->body("Pedido {$pedido->codigo} enviado a cocina.")->success()->send();

        $this->cargarPedidos();
    }

    /**
     * @param  list<array<string, mixed>>  $faltantes
     */
    private function abrirSustitucionRapida(int $pedidoId, array $faltantes): void
    {
        $this->pedidoConFaltantesId = $pedidoId;
        $this->faltantesPreparacion = $faltantes;
        $this->sustitucionData = ['sustitutos' => []];

        foreach ($faltantes as $index => $faltante) {
            $this->sustitucionData['sustitutos'][$index] = null;
        }

        $this->sustitucionForm->fill($this->sustitucionData);
        $this->dispatch('open-modal', id: 'modal-sustitucion-ingredientes');
    }

    public function autorizarSustitucionesEIniciar(): void
    {
        if ($this->pedidoConFaltantesId === null) {
            return;
        }

        try {
            $data = $this->sustitucionForm->getState();
            $sustitutos = is_array($data['sustitutos'] ?? null) ? $data['sustitutos'] : [];

            // Pre-cargar todos los PedidoItems en una sola query (evita N+1)
            $pedidoItemIds = array_filter(
                array_map(
                    fn (mixed $f): int => is_numeric($f['pedido_item_id'] ?? null) ? (int) $f['pedido_item_id'] : 0,
                    $this->faltantesPreparacion,
                ),
                fn (int $id): bool => $id > 0,
            );

            $itemsPorId = PedidoItem::query()
                ->whereIn('id', $pedidoItemIds)
                ->get()
                ->keyBy('id');

            foreach ($this->faltantesPreparacion as $index => $faltante) {
                $sustitutoId = isset($sustitutos[$index]) && is_numeric($sustitutos[$index])
                    ? (int) $sustitutos[$index]
                    : 0;

                if ($sustitutoId <= 0) {
                    throw new DomainException('Debe seleccionar un sustituto para cada ingrediente faltante.');
                }

                $pedidoItemId = is_numeric($faltante['pedido_item_id'] ?? null) ? (int) $faltante['pedido_item_id'] : 0;
                $varianteOriginalId = is_numeric($faltante['variante_original_id'] ?? null) ? (int) $faltante['variante_original_id'] : 0;
                $requeridoVal = is_numeric($faltante['requerido'] ?? null) ? (float) $faltante['requerido'] : 0.0;

                // Lookup O(1) desde la colección pre-cargada, sin query adicional
                $item = $itemsPorId->get($pedidoItemId);

                if (! $item instanceof PedidoItem) {
                    continue;
                }

                $this->autorizarSustitucion->ejecutar(
                    item: $item,
                    varianteOriginalId: $varianteOriginalId,
                    varianteSustitutaId: $sustitutoId,
                    cantidadRequerida: $requeridoVal,
                    cantidadUsada: $requeridoVal,
                    usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
                    motivo: 'Sustitución rápida autorizada desde KDS antes de iniciar preparación.',
                );
            }

            $pedidoId = $this->pedidoConFaltantesId;
            $this->pedidoConFaltantesId = null;
            $this->faltantesPreparacion = [];
            $this->sustitutosSeleccionados = [];
            $this->sustitucionData = [];
            $this->dispatch('close-modal', id: 'modal-sustitucion-ingredientes');

            $pedido = $this->enviarACocina->ejecutarPorId($pedidoId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo autorizar la sustitución')->body($exception->getMessage())->danger()->send();

            return;
        }

        Notification::make()->title('Preparación iniciada')->body("Pedido {$pedido->codigo} enviado a cocina con sustituciones autorizadas.")->success()->send();

        $this->cargarPedidos();
    }

    /** @return array<int, string> */
    public function opcionesSustitutos(int $varianteOriginalId): array
    {
        return ProductoVariante::query()
            ->with(['producto', 'unidadMedida'])
            ->where('id', '!=', $varianteOriginalId)
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante): array {
                $producto = $variante->producto->nombre ?? 'Producto';
                $nombre = $variante->nombre_variante ?: $variante->codigo;
                $unidad = $variante->unidadMedida?->nombre;
                $suffix = $unidad !== null ? " ({$unidad})" : '';

                return [(int) $variante->id => "{$producto} - {$nombre}{$suffix}"];
            })
            ->all();
    }

    public function sustitucionForm(Schema $schema): Schema
    {
        $sections = [];

        foreach ($this->faltantesPreparacion as $index => $faltante) {
            $ingrediente = is_string($faltante['ingrediente'] ?? null) ? $faltante['ingrediente'] : 'Ingrediente';
            $plato = is_string($faltante['plato'] ?? null) ? $faltante['plato'] : 'Platillo';
            $requerido = number_format(is_numeric($faltante['requerido'] ?? null) ? (float) $faltante['requerido'] : 0.0, 2);
            $disponible = number_format(is_numeric($faltante['disponible'] ?? null) ? (float) $faltante['disponible'] : 0.0, 2);
            $faltanteCantidad = number_format(is_numeric($faltante['faltante'] ?? null) ? (float) $faltante['faltante'] : 0.0, 2);
            $varianteOriginalId = is_numeric($faltante['variante_original_id'] ?? null) ? (int) $faltante['variante_original_id'] : 0;

            $sections[] = Section::make($ingrediente)
                ->description("{$plato} · requerido: {$requerido} · disponible: {$disponible} · falta: {$faltanteCantidad}")
                ->icon('heroicon-o-exclamation-triangle')
                ->compact()
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                    ])
                        ->schema([
                            Select::make("sustitutos.{$index}")
                                ->label('Sustituto')
                                ->placeholder('Seleccione producto / variante')
                                ->options(fn (): array => $this->opcionesSustitutos($varianteOriginalId))
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required(),
                        ]),
                ]);
        }

        return $schema
            ->schema($sections)
            ->statePath('sustitucionData');
    }

    public function tiempoTranscurrido(Pedido $pedido): string
    {
        return $pedido->created_at?->diffForHumans(null, CarbonInterface::DIFF_ABSOLUTE) ?? '';
    }

    protected function getPollingInterval(): string
    {
        return '6s';
    }

    private function normalizarAreaCocina(mixed $area): ?string
    {
        if (! is_string($area) || trim($area) === '') {
            return null;
        }

        return match (trim($area)) {
            'barra_bebidas' => 'bar',
            'cocina_caliente', 'cocina_fria' => 'cocina',
            'bar', 'cocina', 'postres', 'parrilla' => trim($area),
            default => null,
        };
    }

    /** @return list<Action|ActionGroup> */
    protected function getHeaderActions(): array
    {
        return [
            MermaGlobalDiariaAction::make()
                ->color('gray'),
            SolicitudAbastecimientoCocinaAction::make()
                ->color('gray'),
            Action::make('verSolicitudesAbastecimiento')
                ->label('Ver Solicitudes')
                ->icon('heroicon-o-document-arrow-up')
                ->color('gray')
                ->url(fn (): string => SolicitudResource::getUrl('index')),
            ActionGroup::make([
                Action::make('conciliacionRecetas')
                    ->label('Conciliar Recetas')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn (): string => ConciliacionRecetasCocina::getUrl()),
                Action::make('materiaPrima')
                    ->label('Materia Prima')
                    ->icon('heroicon-o-beaker')
                    ->url(fn (): string => MateriaPrimaCocina::getUrl()),
                Action::make('trazabilidad')
                    ->label('Trazabilidad')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn (): string => ProcesoCocinaResource::getUrl()),
                Action::make('pantallaTurnos')
                    ->label('Pantalla Turnos KDS')
                    ->icon('heroicon-o-tv')
                    ->url(fn (): string => PantallaPedidos::getUrl())
                    ->openUrlInNewTab(),
                Action::make('estacionBarra')
                    ->label('Estación Barra')
                    ->icon('heroicon-o-beaker')
                    ->url(fn (): string => self::getUrl(['area' => 'bar']))
                    ->openUrlInNewTab(),
                Action::make('estacionParrilla')
                    ->label('Estación Parrilla')
                    ->icon('heroicon-o-fire')
                    ->url(fn (): string => self::getUrl(['area' => 'parrilla']))
                    ->openUrlInNewTab(),
            ])
                ->label('Herramientas')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('gray')
                ->button(),
        ];
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_CocinaPedidos') ?? false;
    }
}
