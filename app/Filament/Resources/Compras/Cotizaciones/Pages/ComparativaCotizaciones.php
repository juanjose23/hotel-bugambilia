<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Models\Compras\Solicitud;
use App\UseCases\Compras\ElegirCotizacionGanadora;
use App\UseCases\Compras\ObtenerRecomendacionLogistica;
use App\UseCases\Compras\ObtenerSolicitudParaComparativa;
use App\UseCases\Compras\SeleccionarItemGanador;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ComparativaCotizaciones extends Page
{
    protected static string $resource = CotizacionResource::class;

    protected string $view = 'filament.resources.compras.cotizaciones.pages.comparativa-cotizaciones';

    protected static ?string $title = 'Comparativa de Precios por Ítem';

    public ?int $solicitudId = null;
    public ?Solicitud $solicitud = null;
    public ?array $recomendacion = null;

    public function mount(): void
    {
        $this->solicitudId = (int) request()->query('solicitud_id');
        
        if (!$this->solicitudId) {
            Notification::make()
                ->title('Error')
                ->body('Se requiere una solicitud para comparar.')
                ->danger()
                ->send();
            $this->redirect(CotizacionResource::getUrl('index'));
            return;
        }

        $this->solicitud = app(ObtenerSolicitudParaComparativa::class)->execute($this->solicitudId);
        
        if ($this->solicitud) {
            $this->calculateRecommendation();
        }
    }

    protected function calculateRecommendation(): void
    {
        $this->recomendacion = app(ObtenerRecomendacionLogistica::class)->execute($this->solicitud);
    }

    public function getComparisonData(): array
    {
        if (!$this->solicitud) return [];

        $items = $this->solicitud->items;
        $cotizaciones = $this->solicitud->cotizaciones;

        $rows = [];
        foreach ($items as $sItem) {
            $itemData = [
                'producto_id' => $sItem->producto_id,
                'producto' => $sItem->producto->nombre,
                'variante_solicitada' => $sItem->variante?->nombre ?? 'Estándar',
                'cantidad' => $sItem->cantidad_aprobada > 0 ? $sItem->cantidad_aprobada : $sItem->cantidad_solicitada,
                'precios' => [],
                'variantes_ofrecidas' => [],
                'mejor_cotizacion_id' => null,
                'mejor_precio' => null,
            ];

            foreach ($cotizaciones as $cot) {
                $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                $precio = $cItem ? $cItem->precio_unitario : null;
                
                $itemData['precios'][$cot->id] = $precio;
                $itemData['variantes_ofrecidas'][$cot->id] = $cItem?->variante?->nombre ?? ($cItem ? 'Estándar' : null);

                if ($precio !== null && ($itemData['mejor_precio'] === null || $precio < $itemData['mejor_precio'])) {
                    $itemData['mejor_precio'] = $precio;
                    $itemData['mejor_cotizacion_id'] = $cot->id;
                }
            }
            $rows[] = $itemData;
        }

        return [
            'cotizaciones' => $cotizaciones,
            'rows' => $rows,
        ];
    }

    public function seleccionarGanadorPorItem(int $productoId, int $cotizacionId): void
    {
        if ($this->solicitud->ordenesCompra()->exists()) {
            Notification::make()->title('Solicitud bloqueada')->body('No se pueden cambiar los ganadores porque ya existen órdenes generadas.')->danger()->send();
            return;
        }

        app(SeleccionarItemGanador::class)->execute($cotizacionId, $productoId);

        Notification::make()
            ->title('Ítem asignado')
            ->success()
            ->send();
    }

    public function seleccionarTodoProveedor(int $cotizacionId): void
    {
        if ($this->solicitud->ordenesCompra()->exists()) {
            Notification::make()->title('Solicitud bloqueada')->body('No se pueden cambiar los ganadores porque ya existen órdenes generadas.')->danger()->send();
            return;
        }

        app(ElegirCotizacionGanadora::class)->execute($cotizacionId);

        Notification::make()
            ->title('Proveedor seleccionado para todos los ítems')
            ->success()
            ->send();
    }

    public function aplicarRecomendacion(): void
    {
        if (!$this->recomendacion) return;

        if ($this->recomendacion['tipo'] === 'PROVEEDOR ÚNICO') {
            app(ElegirCotizacionGanadora::class)->execute($this->recomendacion['cotizacion_id']);
        } else {
            // Aplicar mejor precio por cada item
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

        Notification::make()
            ->title('Recomendación Aplicada')
            ->body('Se han seleccionado los ganadores según la estrategia recomendada.')
            ->success()
            ->send();
            
        $this->redirect(request()->header('Referer'));
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
                ->hidden(fn () => $this->solicitud->ordenesCompra()->exists()),

            Action::make('generarTodas')
                ->label('Generar Órdenes Ganadoras')
                ->icon(Heroicon::ShoppingCart)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Generar todas las órdenes')
                ->modalDescription('Se crearán órdenes de compra para todos los proveedores que tengan ítems ganadores.')
                ->action(function () {
                    $cotizacionesConGanadores = $this->solicitud->cotizaciones()
                        ->whereHas('items', fn ($q) => $q->where('es_elegido', true))
                        ->get();

                    if ($cotizacionesConGanadores->isEmpty()) {
                        Notification::make()->title('Sin ganadores')->body('Primero debe aplicar una recomendación o elegir ganadores.')->warning()->send();
                        return;
                    }

                    $ordenesCreadas = 0;
                    foreach ($cotizacionesConGanadores as $cot) {
                        // Evitar duplicados si ya tiene orden
                        if (!$this->solicitud->ordenesCompra()->where('proveedor_id', $cot->proveedor_id)->exists()) {
                            app(\App\UseCases\Compras\GenerarOrdenDesdeCotizacion::class)->execute($cot->id);
                            $ordenesCreadas++;
                        }
                    }

                    Notification::make()
                        ->title('Proceso Completado')
                        ->body("Se han generado {$ordenesCreadas} órdenes de compra.")
                        ->success()
                        ->send();

                    $this->redirect(\App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource::getUrl('index'));
                })
                ->visible(fn () => $this->solicitud->cotizaciones()->whereHas('items', fn ($q) => $q->where('es_elegido', true))->exists()),

            Action::make('regresar')
                ->label('Volver')
                ->color('gray')
                ->url(CotizacionResource::getUrl('index')),
        ];
    }
}
