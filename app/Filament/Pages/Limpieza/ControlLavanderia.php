<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\Filament\Pages\Limpieza\Schemas\ConsumoJornadaLavanderiaForm;
use App\Filament\Pages\Limpieza\Schemas\ConsumoLavanderiaForm;
use App\Filament\Pages\Limpieza\Schemas\EntradaInsumosLavanderiaForm;
use App\Filament\Pages\Limpieza\Schemas\EntradaLavanderiaForm;
use App\Filament\Pages\Limpieza\Schemas\ReabastecerLavanderiaForm;
use App\Interactors\Limpieza\Lavanderia\RegistrarConsumoJornadaLavanderia;
use App\Interactors\Limpieza\Lavanderia\RegistrarConsumoMermaLavanderia;
use App\Interactors\Limpieza\Lavanderia\RegistrarEntradaDirectaLavanderia;
use App\Interactors\Limpieza\Lavanderia\RegistrarEntradaInsumosLavanderia;
use App\Interactors\Limpieza\Lavanderia\ReponerDesdeLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerCategoriasBlancosLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerInventarioLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesBlancosLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesInsumosQuimicos;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesLotesLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerUbicacionesInventarioLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ResolverUbicacionLavanderia;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

/**
 * @property Schema $form
 * @property Schema $entradaForm
 * @property Schema $entradaInsumosForm
 * @property Schema $jornadaForm
 * @property Schema $consumirForm
 * @property Schema $reabastecerForm
 */
class ControlLavanderia extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected ResolverUbicacionLavanderia $resolverLavanderia;

    protected ObtenerInventarioLavanderia $inventarioLavanderia;

    protected ObtenerOpcionesBlancosLavanderia $opcionesBlancos;

    protected ObtenerCategoriasBlancosLavanderia $categoriasBlancos;

    protected ObtenerUbicacionesInventarioLavanderia $ubicacionesInventarioLavanderia;

    protected ObtenerOpcionesLotesLavanderia $opcionesLotes;

    protected ObtenerOpcionesInsumosQuimicos $opcionesInsumos;

    public function boot(
        ResolverUbicacionLavanderia $resolverLavanderia,
        ObtenerInventarioLavanderia $inventarioLavanderia,
        ObtenerOpcionesBlancosLavanderia $opcionesBlancos,
        ObtenerCategoriasBlancosLavanderia $categoriasBlancos,
        ObtenerUbicacionesInventarioLavanderia $ubicacionesInventarioLavanderia,
        ObtenerOpcionesLotesLavanderia $opcionesLotes,
        ObtenerOpcionesInsumosQuimicos $opcionesInsumos,
    ): void {
        $this->resolverLavanderia = $resolverLavanderia;
        $this->inventarioLavanderia = $inventarioLavanderia;
        $this->opcionesBlancos = $opcionesBlancos;
        $this->categoriasBlancos = $categoriasBlancos;
        $this->ubicacionesInventarioLavanderia = $ubicacionesInventarioLavanderia;
        $this->opcionesLotes = $opcionesLotes;
        $this->opcionesInsumos = $opcionesInsumos;
    }

    protected string $view = 'filament.resources.limpieza.control-lavanderia';

    protected static ?string $slug = 'limpieza/control-lavanderia';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza & Lavandería';

    protected static ?string $navigationLabel = 'Control de Lavandería';

    protected static ?string $title = 'Control de Inventario de Lavandería';

    protected static ?int $navigationSort = 5;

    public ?int $lavanderiaId = null;

    public ?string $activeTab = 'inventario'; // inventario, entrada, entrada_insumos, jornada, consumir, reabastecer

    /** @var array<string, mixed>|null */
    public ?array $entradaData = [];

    /** @var array<string, mixed>|null */
    public ?array $entradaInsumosData = [];

    /** @var array<string, mixed>|null */
    public ?array $jornadaData = [];

    /** @var array<string, mixed>|null */
    public ?array $consumirData = [];

    /** @var array<string, mixed>|null */
    public ?array $reabastecerData = [];

    public function mount(): void
    {
        $this->lavanderiaId = $this->resolverLavanderia->execute()->id;

        $this->entradaForm->fill();
        $this->entradaInsumosForm->fill();
        $this->jornadaForm->fill();
        $this->consumirForm->fill();
        $this->reabastecerForm->fill();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->inventarioLavanderia->execute($this->ubicacionesInventarioLavanderia->execute()))
            ->columns([
                TextColumn::make('variante.producto.nombre')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variante.producto.categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('variante.nombre_variante')
                    ->label('Variante / Medida')
                    ->placeholder('Estándar'),
                TextColumn::make('lote.codigo_lote')
                    ->label('Lote')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('cantidad')
                    ->label('Stock en Lavandería')
                    ->numeric(decimalPlaces: 2)
                    ->badge()
                    ->color('success')
                    ->weight('bold')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('variante.producto.unidadMedida.nombre')
                    ->label('Unidad')
                    ->color('gray')
                    ->placeholder('piezas'),
            ])
            ->defaultSort('id', 'desc');
    }

    /** @return array<string, Schema> */
    protected function getForms(): array
    {
        return [
            'entradaForm' => $this->makeSchema()
                ->schema(EntradaLavanderiaForm::schema(
                    $this->categoriasBlancos,
                    $this->opcionesBlancos,
                    $this->opcionesLotes,
                ))
                ->statePath('entradaData'),

            'entradaInsumosForm' => $this->makeSchema()
                ->schema(EntradaInsumosLavanderiaForm::schema(
                    $this->opcionesInsumos,
                    $this->opcionesLotes,
                ))
                ->statePath('entradaInsumosData'),

            'jornadaForm' => $this->makeSchema()
                ->schema(ConsumoJornadaLavanderiaForm::schema(
                    $this->ubicacionesInventarioLavanderia,
                ))
                ->statePath('jornadaData'),

            'consumirForm' => $this->makeSchema()
                ->schema(ConsumoLavanderiaForm::schema(
                    $this->ubicacionesInventarioLavanderia,
                ))
                ->statePath('consumirData'),

            'reabastecerForm' => $this->makeSchema()
                ->schema(ReabastecerLavanderiaForm::schema(
                    $this->ubicacionesInventarioLavanderia,
                ))
                ->statePath('reabastecerData'),
        ];
    }

    public function submitEntrada(RegistrarEntradaDirectaLavanderia $registrarEntrada): void
    {
        $data = $this->entradaForm->getState();
        if (empty($data)) {
            return;
        }

        $tipoOrigen = isset($data['tipo_origen']) && is_string($data['tipo_origen']) && trim($data['tipo_origen']) !== ''
            ? trim($data['tipo_origen'])
            : null;
        $origenId = isset($data['origen_id']) && is_numeric($data['origen_id']) && (int) $data['origen_id'] > 0
            ? (int) $data['origen_id']
            : null;

        if (empty($tipoOrigen) || $origenId === null) {
            Notification::make()
                ->title('Origen Requerido')
                ->body('Debe seleccionar el tipo y origen específico de los blancos para garantizar la trazabilidad.')
                ->warning()
                ->send();

            return;
        }

        $items = $data['items'] ?? [];
        if (! is_array($items) || empty($items)) {
            Notification::make()
                ->title('Error')
                ->body('Debe agregar al menos un producto.')
                ->danger()
                ->send();

            return;
        }

        /** @var list<array{producto_variante_id: int, lote_id?: int|null, cantidad: float, notas?: string|null}> $itemsValidos */
        $itemsValidos = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $varianteId = isset($item['producto_variante_id']) && is_numeric($item['producto_variante_id'])
                ? (int) $item['producto_variante_id']
                : 0;
            $loteId = isset($item['lote_id']) && is_numeric($item['lote_id']) && (int) $item['lote_id'] > 0
                ? (int) $item['lote_id']
                : null;
            $cantidad = isset($item['cantidad']) && is_numeric($item['cantidad'])
                ? (float) $item['cantidad']
                : 0.0;
            $notasItem = isset($item['notas']) && is_string($item['notas']) && trim($item['notas']) !== ''
                ? trim($item['notas'])
                : null;

            if ($varianteId > 0 && $cantidad > 0.0) {
                $itemsValidos[] = [
                    'producto_variante_id' => $varianteId,
                    'lote_id' => $loteId,
                    'cantidad' => $cantidad,
                    'notas' => $notasItem,
                ];
            }
        }

        if (empty($itemsValidos)) {
            Notification::make()
                ->title('Atención')
                ->body('Debe especificar una cantidad mayor a cero en al menos un producto.')
                ->warning()
                ->send();

            return;
        }

        try {
            $notasGenerales = isset($data['notas']) && is_string($data['notas']) && trim($data['notas']) !== ''
                ? trim($data['notas'])
                : null;
            $userId = auth()->id();

            $resultado = $registrarEntrada->ejecutarLote(
                items: $itemsValidos,
                ubicacionLavanderiaId: (int) $this->lavanderiaId,
                creadoPorId: $userId !== null ? (int) $userId : null,
                notasGenerales: $notasGenerales,
                tipoOrigen: $tipoOrigen,
                origenId: $origenId,
            );

            Notification::make()
                ->title('Entrada registrada')
                ->body("Se ingresaron {$resultado['total_piezas']} piezas ({$resultado['total_items']} productos) a lavandería correctamente.")
                ->success()
                ->send();

            $this->entradaForm->fill();
            $this->activeTab = 'inventario';
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitEntradaInsumos(RegistrarEntradaInsumosLavanderia $registrarEntradaInsumos): void
    {
        $data = $this->entradaInsumosForm->getState();
        if (empty($data)) {
            return;
        }

        $tipoOrigen = isset($data['tipo_origen']) && is_string($data['tipo_origen']) ? $data['tipo_origen'] : 'bodega';
        $bodegaOrigenId = isset($data['bodega_origen_id']) && is_numeric($data['bodega_origen_id']) ? (int) $data['bodega_origen_id'] : null;
        $docRef = isset($data['documento_referencia']) && is_string($data['documento_referencia']) ? $data['documento_referencia'] : null;
        $notasGenerales = isset($data['notas_generales']) && is_string($data['notas_generales']) ? $data['notas_generales'] : null;
        $items = $data['items'] ?? [];

        if (! is_array($items) || empty($items)) {
            Notification::make()
                ->title('Atención')
                ->body('Debe agregar al menos un insumo químico.')
                ->warning()
                ->send();

            return;
        }

        /** @var list<array{producto_variante_id: int, cantidad: float, lote_id?: int|null, codigo_lote?: string|null, costo_unitario?: float|null, fecha_vencimiento?: string|null, notas?: string|null}> $itemsList */
        $itemsList = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $varianteId = isset($item['producto_variante_id']) && is_numeric($item['producto_variante_id']) ? (int) $item['producto_variante_id'] : 0;
            $cantidad = isset($item['cantidad']) && is_numeric($item['cantidad']) ? (float) $item['cantidad'] : 0.0;
            $loteId = isset($item['lote_id']) && is_numeric($item['lote_id']) && (int) $item['lote_id'] > 0 ? (int) $item['lote_id'] : null;
            $codigoLote = isset($item['codigo_lote']) && is_string($item['codigo_lote']) && trim($item['codigo_lote']) !== '' ? trim($item['codigo_lote']) : null;
            $costoUnitario = isset($item['costo_unitario']) && is_numeric($item['costo_unitario']) ? (float) $item['costo_unitario'] : null;
            $fechaVenc = isset($item['fecha_vencimiento']) && is_string($item['fecha_vencimiento']) ? $item['fecha_vencimiento'] : null;
            $notasItem = isset($item['notas']) && is_string($item['notas']) ? $item['notas'] : null;

            if ($varianteId > 0 && $cantidad > 0.0) {
                $itemsList[] = [
                    'producto_variante_id' => $varianteId,
                    'cantidad' => $cantidad,
                    'lote_id' => $loteId,
                    'codigo_lote' => $codigoLote,
                    'costo_unitario' => $costoUnitario,
                    'fecha_vencimiento' => $fechaVenc,
                    'notas' => $notasItem,
                ];
            }
        }

        if (empty($itemsList)) {
            Notification::make()
                ->title('Atención')
                ->body('Debe ingresar una cantidad mayor a cero en los insumos.')
                ->warning()
                ->send();

            return;
        }

        try {
            $userId = auth()->id();

            $resultado = $registrarEntradaInsumos->execute(
                tipoOrigen: $tipoOrigen,
                items: $itemsList,
                ubicacionLavanderiaId: $this->ubicacionesInventarioLavanderia->execute(),
                bodegaOrigenId: $bodegaOrigenId,
                creadoPorId: $userId !== null ? (int) $userId : null,
                documentoReferencia: $docRef,
                notasGenerales: $notasGenerales,
            );

            Notification::make()
                ->title('Insumos Ingresados')
                ->body("Se ingresaron {$resultado['total_items']} insumos ({$resultado['total_cantidad']} unidades/litros) al stock de lavandería con éxito.")
                ->success()
                ->send();

            $this->entradaInsumosForm->fill();
            $this->activeTab = 'inventario';
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitConsumir(RegistrarConsumoMermaLavanderia $registrarConsumo): void
    {
        $data = $this->consumirForm->getState();
        if (empty($data)) {
            return;
        }

        try {
            $stockId = isset($data['stock_id']) && is_numeric($data['stock_id']) ? (int) $data['stock_id'] : 0;
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $notas = isset($data['notas']) && is_string($data['notas']) ? $data['notas'] : null;
            $userId = auth()->id();

            $registrarConsumo->execute(
                stockId: $stockId,
                cantidad: $cantidad,
                lavanderiaId: $this->ubicacionesInventarioLavanderia->execute(),
                creadoPorId: $userId !== null ? (int) $userId : null,
                notas: $notas,
            );

            Notification::make()
                ->title('Consumo Registrado')
                ->body('Se registró el consumo/merma correctamente.')
                ->success()
                ->send();

            $this->consumirForm->fill();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitJornada(RegistrarConsumoJornadaLavanderia $registrarJornada): void
    {
        $data = $this->jornadaForm->getState();
        if (empty($data)) {
            return;
        }

        $insumos = $data['insumos'] ?? [];
        if (! is_array($insumos) || empty($insumos)) {
            Notification::make()
                ->title('Atención')
                ->body('Debe agregar al menos un insumo químico utilizado.')
                ->warning()
                ->send();

            return;
        }

        try {
            $fecha = isset($data['fecha']) && is_string($data['fecha']) ? $data['fecha'] : now()->toDateString();
            $turno = isset($data['turno_id']) && is_numeric($data['turno_id'])
                ? (int) $data['turno_id']
                : (isset($data['turno']) && is_string($data['turno']) ? $data['turno'] : 'manana');
            $operador = isset($data['operador_nombre']) && is_string($data['operador_nombre']) ? $data['operador_nombre'] : null;
            $kilos = isset($data['kilos_lavados']) && is_numeric($data['kilos_lavados']) ? (float) $data['kilos_lavados'] : null;
            $observaciones = isset($data['observaciones']) && is_string($data['observaciones']) ? $data['observaciones'] : null;
            $sinMermas = ! empty($data['sin_mermas']);
            $userId = auth()->id();

            /** @var list<array{stock_id: int, cantidad: float, notas?: string|null}> $insumosList */
            $insumosList = [];
            foreach ($insumos as $insumo) {
                if (is_array($insumo) && isset($insumo['stock_id']) && is_numeric($insumo['stock_id']) && isset($insumo['cantidad']) && is_numeric($insumo['cantidad'])) {
                    $insumosList[] = [
                        'stock_id' => (int) $insumo['stock_id'],
                        'cantidad' => (float) $insumo['cantidad'],
                        'notas' => isset($insumo['notas']) && is_string($insumo['notas']) ? $insumo['notas'] : null,
                    ];
                }
            }

            /** @var list<array{stock_id: int, cantidad: float, notas?: string|null}> $mermasList */
            $mermasList = [];
            if (! $sinMermas && isset($data['mermas']) && is_array($data['mermas'])) {
                foreach ($data['mermas'] as $merma) {
                    if (is_array($merma) && isset($merma['stock_id']) && is_numeric($merma['stock_id']) && isset($merma['cantidad']) && is_numeric($merma['cantidad'])) {
                        $mermasList[] = [
                            'stock_id' => (int) $merma['stock_id'],
                            'cantidad' => (float) $merma['cantidad'],
                            'notas' => isset($merma['notas']) && is_string($merma['notas']) ? $merma['notas'] : null,
                        ];
                    }
                }
            }

            $resultado = $registrarJornada->execute(
                ubicacionLavanderiaId: $this->ubicacionesInventarioLavanderia->execute(),
                fechaJornada: $fecha,
                turno: $turno,
                insumos: $insumosList,
                operadorNombre: $operador,
                kilosLavados: $kilos,
                creadoPorId: $userId !== null ? (int) $userId : null,
                observacionesGenerales: $observaciones,
                mermas: $mermasList,
            );

            $mensaje = "Se registraron {$resultado['total_insumos']} insumos consumidos ({$resultado['total_cantidad']} unidades/litros)";
            if ($resultado['total_mermas'] > 0) {
                $mensaje .= " y {$resultado['total_mermas']} mermas reportadas";
            }
            $mensaje .= ' con éxito.';

            Notification::make()
                ->title('Jornada de Lavado Registrada')
                ->body($mensaje)
                ->success()
                ->send();

            $this->jornadaForm->fill();
            $this->activeTab = 'inventario';
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitReabastecer(ReponerDesdeLavanderia $reponerDesdeLavanderia): void
    {
        $data = $this->reabastecerForm->getState();
        if (empty($data)) {
            return;
        }

        $itemsData = $data['items'] ?? [];
        if (! is_array($itemsData) || empty($itemsData)) {
            Notification::make()
                ->title('Error')
                ->body('Debe agregar al menos un insumo para reponer.')
                ->danger()
                ->send();

            return;
        }

        try {
            $tipoDestino = isset($data['tipo_destino']) && is_string($data['tipo_destino']) ? $data['tipo_destino'] : '';
            $destinoId = isset($data['destino_id']) && is_numeric($data['destino_id']) ? (int) $data['destino_id'] : 0;

            $userId = auth()->id();

            foreach ($itemsData as $item) {
                $itemArr = is_array($item) ? $item : [];
                $stockId = isset($itemArr['stock_id']) && is_numeric($itemArr['stock_id']) ? (int) $itemArr['stock_id'] : 0;
                $cantidad = isset($itemArr['cantidad']) && is_numeric($itemArr['cantidad']) ? (float) $itemArr['cantidad'] : 0.0;

                $reponerDesdeLavanderia->execute(
                    stockId: $stockId,
                    cantidad: $cantidad,
                    ubicacionLavanderiaId: $this->ubicacionesInventarioLavanderia->execute(incluirSucios: false),
                    tipoDestino: $tipoDestino,
                    destinoId: $destinoId,
                    creadoPorId: $userId !== null ? (int) $userId : null,
                );
            }

            Notification::make()
                ->title('Reposición Completada')
                ->body('Se reabasteció la ubicación correctamente.')
                ->success()
                ->send();

            $this->reabastecerForm->fill();
            $this->activeTab = 'inventario';
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
