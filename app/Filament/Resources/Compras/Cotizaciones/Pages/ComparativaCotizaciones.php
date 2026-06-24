<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Solicitud;
use App\Services\Compras\NotificadorCompras;
use App\UseCases\Compras\Cotizaciones\Mutations\ElegirCotizacionGanadora;
use App\UseCases\Compras\Cotizaciones\Mutations\SeleccionarItemGanador;
use App\UseCases\Compras\OrdenesCompra\Mutations\GenerarOrdenesDesdeComparativa;
use App\UseCases\Compras\OrdenesCompra\Queries\ObtenerRecomendacionLogistica;
use App\UseCases\Compras\Solicitudes\Queries\ObtenerSolicitudParaComparativa;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class ComparativaCotizaciones extends Page
{
    protected static string $resource = CotizacionResource::class;

    protected string $view = 'filament.resources.compras.cotizaciones.pages.comparativa-cotizaciones';

    protected static ?string $title = 'Comparativa de Precios por Ítem';

    public ?int $solicitudId = null;

    public ?Solicitud $solicitud = null;

    /** @var array<string, mixed>|null */
    public ?array $recomendacion = null;

    protected function loadSolicitud(): void
    {
        if ($this->solicitudId === null) {
            return;
        }

        $this->solicitud = app(ObtenerSolicitudParaComparativa::class)->execute($this->solicitudId);
        if ($this->solicitud) {
            $this->calculateRecommendation();
        }
    }

    public function mount(): void
    {
        Gate::authorize('Compras:ViewComparativaSolicitud');

        $this->solicitudId = (int) request()->query('solicitud_id');

        if (! $this->solicitudId) {
            Notification::make()
                ->title('Error')
                ->body('Se requiere una solicitud para comparar.')
                ->danger()
                ->send();
            $this->redirect(CotizacionResource::getUrl('index'));

            return;
        }

        $this->loadSolicitud();
    }

    protected function calculateRecommendation(): void
    {
        if ($this->solicitud === null) {
            return;
        }

        $this->recomendacion = app(ObtenerRecomendacionLogistica::class)->execute($this->solicitud);
    }

    /** @return array<string, mixed> */
    public function getComparisonData(): array
    {
        if (! $this->solicitud) {
            return [];
        }

        $items = $this->solicitud->items;
        $cotizaciones = $this->solicitud->cotizaciones;

        $rows = [];
        foreach ($items as $sItem) {
            $itemData = [
                'producto_id' => $sItem->producto_id,
                'producto' => $sItem->producto ? $sItem->producto->nombre : '',
                'variante_solicitada' => $sItem->variante->nombre_variante ?? 'Estándar',

                'cantidad' => $sItem->cantidad_aprobada > 0 ? $sItem->cantidad_aprobada : $sItem->cantidad_solicitada,
                'precios' => [],
                'variantes_ofrecidas' => [],
                'mejor_cotizacion_id' => null,
                'mejor_precio' => null,
                'ganador' => null,
            ];

            foreach ($cotizaciones as $cot) {
                $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                $precio = $cItem ? $cItem->precio_unitario : null;

                $itemData['precios'][$cot->id] = $precio;
                $itemData['variantes_ofrecidas'][$cot->id] = $cItem?->variante->nombre_variante ?? ($cItem ? 'Estándar' : null);

                if ($precio !== null && ($itemData['mejor_precio'] === null || $precio < $itemData['mejor_precio'])) {
                    $itemData['mejor_precio'] = $precio;
                    $itemData['mejor_cotizacion_id'] = $cot->id;
                }

                if ($cItem?->es_elegido) {
                    $itemData['ganador'] = [
                        'cotizacion_id' => $cot->id,
                        'proveedor' => $this->getProveedorNombre($cot),
                        'precio' => $precio,
                    ];
                }
            }
            $rows[] = $itemData;
        }

        return [
            'cotizaciones' => $cotizaciones,
            'rows' => $rows,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function getWinnersData(): array
    {
        if (! $this->solicitud) {
            return [];
        }

        $winners = [];
        foreach ($this->solicitud->items as $sItem) {
            foreach ($this->solicitud->cotizaciones as $cot) {
                $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                if ($cItem?->es_elegido) {
                    $winners[] = [
                        'producto' => $sItem->producto ? $sItem->producto->nombre : '',
                        'variante' => $sItem->variante->nombre_variante ?? 'Estándar',
                        'cantidad' => $sItem->cantidad_aprobada > 0 ? $sItem->cantidad_aprobada : $sItem->cantidad_solicitada,
                        'proveedor' => $this->getProveedorNombre($cot),
                        'precio_unitario' => $cItem->precio_unitario,
                        'subtotal' => $cItem->subtotal,
                        'cotizacion_id' => $cot->id,
                        'orden_generada' => OrdenCompra::where('cotizacion_id', $cot->id)
                            ->whereHas('items', fn ($q) => $q->where('producto_id', $sItem->producto_id))
                            ->where('estado', '!=', EstadoOrdenCompra::Cancelada)
                            ->exists(),
                    ];
                }
            }
        }

        return $winners;
    }

    public function seleccionarGanadorPorItem(int $productoId, int $cotizacionId): void
    {
        if ($this->solicitud === null) {
            return;
        }

        if ($this->solicitud->ordenesCompra()->where('estado', '!=', EstadoOrdenCompra::Cancelada)->exists()) {
            Notification::make()->title('Solicitud bloqueada')->body('No se pueden cambiar los ganadores porque ya existen órdenes activas.')->danger()->send();

            return;
        }

        app(SeleccionarItemGanador::class)->execute($cotizacionId, $productoId);

        $cotizacion = Cotizacion::find($cotizacionId);
        if ($cotizacion) {
            app(NotificadorCompras::class)->ganadorSeleccionado($cotizacion);
        }

        $this->loadSolicitud();

        Notification::make()
            ->title('Ítem asignado')
            ->success()
            ->send();
    }

    public function seleccionarTodoProveedor(int $cotizacionId): void
    {
        if ($this->solicitud === null) {
            return;
        }

        if ($this->solicitud->ordenesCompra()->where('estado', '!=', EstadoOrdenCompra::Cancelada)->exists()) {
            Notification::make()->title('Solicitud bloqueada')->body('No se pueden cambiar los ganadores porque ya existen órdenes activas.')->danger()->send();

            return;
        }

        app(ElegirCotizacionGanadora::class)->execute($cotizacionId);

        $cotizacion = Cotizacion::find($cotizacionId);
        if ($cotizacion) {
            app(NotificadorCompras::class)->ganadorSeleccionado($cotizacion);
        }

        $this->loadSolicitud();

        Notification::make()
            ->title('Proveedor seleccionado para todos los ítems')
            ->success()
            ->send();
    }

    public function aplicarRecomendacion(): void
    {
        if ($this->solicitud === null) {
            return;
        }

        if (! $this->recomendacion) {
            return;
        }

        if ($this->recomendacion['tipo'] === 'PROVEEDOR ÚNICO') {
            $recomendacionId = $this->recomendacion['cotizacion_id'];
            $cotizacionId = is_numeric($recomendacionId) ? intval($recomendacionId) : 0;
            app(ElegirCotizacionGanadora::class)->execute($cotizacionId);
        } else {
            foreach ($this->solicitud->items as $sItem) {
                $mejorPrecio = null;
                $mejorCotId = null;

                foreach ($this->solicitud->cotizaciones as $cot) {
                    $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                    if ($cItem && ($mejorPrecio === null || $cItem->precio_unitario < $mejorPrecio)) {
                        $mejorPrecio = $cItem->precio_unitario;
                        $mejorCotId = $cot->id;
                    }
                }

                if ($mejorCotId) {
                    app(SeleccionarItemGanador::class)->execute($mejorCotId, $sItem->producto_id);
                }
            }
        }

        $this->loadSolicitud();

        Notification::make()
            ->title('Recomendación Aplicada')
            ->body('Se han seleccionado los ganadores según la estrategia recomendada.')
            ->success()
            ->send();

        if ($this->solicitud) {
            app(NotificadorCompras::class)->solicitudAprobada($this->solicitud);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('aplicar')
                ->label('Aplicar Recomendación')
                ->icon(Heroicon::Sparkles)
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->aplicarRecomendacion())
                ->hidden(fn () => $this->solicitud?->ordenesCompra()->where('estado', '!=', EstadoOrdenCompra::Cancelada)->exists() ?? true),

            Action::make('generarTodas')
                ->label('Generar Órdenes Ganadoras')
                ->icon(Heroicon::ShoppingCart)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Generar todas las órdenes')
                ->modalDescription('Se creará una orden de compra por cada proveedor que tenga ítems ganadores.')
                ->action(function () {
                    if ($this->solicitud === null) {
                        return;
                    }

                    $this->solicitud->refresh();

                    $ordenesCreadas = app(GenerarOrdenesDesdeComparativa::class)->execute($this->solicitud->id);

                    if ($ordenesCreadas > 0) {
                        Notification::make()
                            ->title('Proceso Completado')
                            ->body("Se han generado {$ordenesCreadas} órdenes de compra.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Aviso')
                            ->body('No se generaron órdenes nuevas. Seleccione ganadores primero o ya existen órdenes para estos proveedores.')
                            ->info()
                            ->send();
                    }

                    $this->redirect(OrdenCompraResource::getUrl('index'));
                })
                ->visible(fn () => $this->solicitud?->cotizaciones()->whereHas('items', fn ($q) => $q->where('es_elegido', true))->exists() ?? false)
                ->hidden(fn () => $this->solicitud?->ordenesCompra()->where('estado', '!=', EstadoOrdenCompra::Cancelada)->exists() ?? true),

            Action::make('imprimirReporte')
                ->label('Imprimir Comparativo')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn () => route('reporte.comparativa', ['solicitud' => $this->solicitudId]))
                ->openUrlInNewTab()
                ->visible(fn () => ($this->solicitud?->cotizaciones()->exists() ?? false) && auth()->user()?->can('Compras:ImprimirComparativa') ?? false),

            Action::make('verCotizaciones')
                ->label('Ver Todas')
                ->icon(Heroicon::ListBullet)
                ->color('gray')
                ->url(fn () => CotizacionResource::getUrl('index', [
                    'filters[solicitud_id][value]' => $this->solicitudId,
                ])),

            Action::make('regresar')
                ->label('Volver')
                ->color('gray')
                ->url(CotizacionResource::getUrl('index')),
        ];
    }

    private function getProveedorNombre(Cotizacion $cot): string
    {
        $proveedor = $cot->proveedor;
        if (! $proveedor) {
            return "Proveedor #{$cot->proveedor_id}";
        }

        $persona = $proveedor->persona;
        if (! $persona) {
            return "Proveedor #{$cot->proveedor_id}";
        }

        if ($persona->personaJuridica) {
            return $persona->personaJuridica->razon_social;
        }

        return $persona->primer_nombre ?? "Proveedor #{$cot->proveedor_id}";
    }
}
