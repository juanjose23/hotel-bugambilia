<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Actions\Compras\Cotizaciones\GenerarReporteComparativaPdfAction;
use App\BusinessLogic\Compras\VerificarSolicitudBloqueada;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Events\Compras\GanadorSeleccionado;
use App\Events\Compras\SolicitudAprobada;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Interactors\Compras\Cotizaciones\AplicarRecomendacionLogistica;
use App\Interactors\Compras\Cotizaciones\ElegirCotizacionGanadora;
use App\Interactors\Compras\Cotizaciones\ObtenerDatosComparativa;
use App\Interactors\Compras\Cotizaciones\SeleccionarItemGanador;
use App\Interactors\Compras\OrdenesCompra\GenerarOrdenesDesdeComparativa;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Cotizaciones\ObtenerGanadoresComparativa;
use App\Repository\Queries\Compras\Cotizaciones\ObtenerRecomendacionLogistica;
use App\Repository\Queries\Compras\Solicitudes\ObtenerSolicitudParaComparativa;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

/**
 * SRP VIOLATION: 6 constructor dependencies. This page handles selection,
 * recommendation, notification, and order generation. Should be decomposed.
 */
class ComparativaCotizaciones extends Page
{
    protected static string $resource = CotizacionResource::class;

    protected ObtenerSolicitudParaComparativa $solicitudParaComparativa;

    protected ObtenerRecomendacionLogistica $recomendacionLogistica;

    protected ObtenerDatosComparativa $obtenerDatosComparativa;

    protected ObtenerGanadoresComparativa $obtenerGanadoresComparativa;

    protected VerificarSolicitudBloqueada $verificarBloqueo;

    protected AplicarRecomendacionLogistica $aplicarRecomendacionLogistica;

    protected SeleccionarItemGanador $seleccionarItemGanador;

    protected ElegirCotizacionGanadora $elegirCotizacionGanadora;

    protected GenerarOrdenesDesdeComparativa $generarOrdenesDesdeComparativa;

    public function boot(
        ObtenerSolicitudParaComparativa $solicitudParaComparativa,
        ObtenerRecomendacionLogistica $recomendacionLogistica,
        ObtenerDatosComparativa $obtenerDatosComparativa,
        ObtenerGanadoresComparativa $obtenerGanadoresComparativa,
        VerificarSolicitudBloqueada $verificarBloqueo,
        AplicarRecomendacionLogistica $aplicarRecomendacionLogistica,
        SeleccionarItemGanador $seleccionarItemGanador,
        ElegirCotizacionGanadora $elegirCotizacionGanadora,
        GenerarOrdenesDesdeComparativa $generarOrdenesDesdeComparativa,
    ): void {
        $this->solicitudParaComparativa = $solicitudParaComparativa;
        $this->recomendacionLogistica = $recomendacionLogistica;
        $this->obtenerDatosComparativa = $obtenerDatosComparativa;
        $this->obtenerGanadoresComparativa = $obtenerGanadoresComparativa;
        $this->verificarBloqueo = $verificarBloqueo;
        $this->aplicarRecomendacionLogistica = $aplicarRecomendacionLogistica;
        $this->seleccionarItemGanador = $seleccionarItemGanador;
        $this->elegirCotizacionGanadora = $elegirCotizacionGanadora;
        $this->generarOrdenesDesdeComparativa = $generarOrdenesDesdeComparativa;
    }

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

        $this->solicitud = $this->solicitudParaComparativa->ejecutar($this->solicitudId);
        if ($this->solicitud) {
            $this->calculateRecommendation();
        }
    }

    public function mount(): void
    {
        if (! auth()->user()?->hasRole('super_admin')) {
            Gate::authorize('Compras:ViewComparativaSolicitud');
        }

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

        $this->recomendacion = $this->recomendacionLogistica->ejecutar($this->solicitud);
    }

    /** @return array<string, mixed> */
    public function getComparisonData(): array
    {
        if ($this->solicitud === null) {
            return [];
        }

        return $this->obtenerDatosComparativa->buildComparisonData($this->solicitud);
    }

    /** @return array<int, array{producto: string, variante: string, proveedor: string, precio_unitario: float, subtotal: float, cotizacion_id: int, orden_generada: bool}> */
    public function getWinnersData(): array
    {
        if ($this->solicitud === null) {
            return [];
        }

        return $this->obtenerGanadoresComparativa->ejecutar($this->solicitud)->all();
    }

    public function seleccionarGanadorPorItem(int $productoId, int $cotizacionId): void
    {
        if ($this->solicitud === null) {
            return;
        }

        if ($this->verificarBloqueo->estaBloqueada($this->solicitud)) {
            Notification::make()->title('Solicitud bloqueada')->body('No se pueden cambiar los ganadores porque ya existen órdenes activas.')->danger()->send();

            return;
        }

        $this->seleccionarItemGanador->ejecutar($cotizacionId, $productoId);

        $cotizacion = Cotizacion::find($cotizacionId);
        if ($cotizacion) {
            GanadorSeleccionado::dispatch($cotizacion);
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

        if ($this->verificarBloqueo->estaBloqueada($this->solicitud)) {
            Notification::make()->title('Solicitud bloqueada')->body('No se pueden cambiar los ganadores porque ya existen órdenes activas.')->danger()->send();

            return;
        }

        $this->elegirCotizacionGanadora->ejecutar($cotizacionId);

        $cotizacion = Cotizacion::find($cotizacionId);
        if ($cotizacion) {
            GanadorSeleccionado::dispatch($cotizacion);
        }

        $this->loadSolicitud();

        Notification::make()
            ->title('Proveedor seleccionado para todos los ítems')
            ->success()
            ->send();
    }

    public function aplicarRecomendacion(): void
    {
        if ($this->solicitud === null || $this->recomendacion === null) {
            return;
        }

        $this->aplicarRecomendacionLogistica->ejecutar($this->solicitud, $this->recomendacion);

        $this->loadSolicitud();

        Notification::make()
            ->title('Recomendación Aplicada')
            ->body('Se han seleccionado los ganadores según la estrategia recomendada.')
            ->success()
            ->send();

        if ($this->solicitud !== null) {
            SolicitudAprobada::dispatch($this->solicitud);
        }
    }

    protected function getHeaderActions(): array
    {

        return [
            Action::make('aplicar')
                ->label('Aplicar Recomendación')
                ->icon(Heroicon::CheckBadge)
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

                    $ordenesCreadas = $this->generarOrdenesDesdeComparativa->ejecutar($this->solicitud->id);

                    if ($ordenesCreadas > 0) {
                        Notification::make()
                            ->title('Proceso Completado')
                            ->body("Se han generado $ordenesCreadas órdenes de compra.")
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
                ->action(action: function () {
                    if ($this->solicitud === null) {
                        return null;
                    }
                    $pdf = app(GenerarReporteComparativaPdfAction::class)
                        ->ejecutar($this->solicitud);

                    return response()->stream(fn () => print ($pdf->output()), 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="HTB-COM-006-Comparativa.pdf"',
                    ]);
                })
                ->visible(fn () => ($this->solicitud?->cotizaciones()->exists() ?? false) && (auth()->user()?->can('Compras:ImprimirComparativa') ?? false)),

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
}
