<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Cotizaciones\AnalizarScoringCotizaciones;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ComparativaSolicitud extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SolicitudResource::class;

    protected AnalizarScoringCotizaciones $analizarScoring;

    public function boot(AnalizarScoringCotizaciones $analizarScoring): void
    {
        $this->analizarScoring = $analizarScoring;
    }

    protected string $view = 'filament.resources.compras.solicitudes.pages.comparativa-solicitud';

    protected static ?string $title = 'Comparativa de Cotizaciones';

    public ?int $recomendadaId = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    /**
     * Algoritmo de recomendación basado en Scoring (Delegado a UseCase)
     */
    public function analizarMejorOpcion(): void
    {
        /** @var Solicitud $solicitud */
        $solicitud = $this->record;
        $ganadoraId = $this->analizarScoring->ejecutar($solicitud);

        if (! $ganadoraId) {
            Notification::make()
                ->title('Análisis no disponible')
                ->body('Se necesitan al menos 2 cotizaciones para realizar la comparativa.')
                ->warning()
                ->send();

            return;
        }

        $this->recomendadaId = $ganadoraId;

        Notification::make()
            ->title('Análisis completado')
            ->body('El sistema ha identificado la opción más equilibrada entre costo y tiempo mediante el algoritmo de scoring.')
            ->success()
            ->send();
    }

    /** @return array<int, array{id: int, proveedor: string, empresa: string, total: float, dias_entrega: int, condicion_pago: string, es_ganadora: bool, es_mas_barato: bool, es_mas_rapido: bool, es_recomendada: bool, observaciones: string|null, archivo: string|null}> */
    public function getCotizaciones(): array
    {
        /** @var Solicitud $solicitud */
        $solicitud = $this->record;
        $cotizaciones = $solicitud->cotizaciones()->with(['proveedor.persona.personaNatural', 'proveedor.persona.personaJuridica', 'condicionPago'])->get();

        if ($cotizaciones->isEmpty()) {
            return [];
        }

        $minTotal = $cotizaciones->min('total');
        $minDias = $cotizaciones->min('dias_entrega');

        /** @var array<int, array{id: int, proveedor: string, empresa: string, total: float, dias_entrega: int, condicion_pago: string, es_ganadora: bool, es_mas_barato: bool, es_mas_rapido: bool, es_recomendada: bool, observaciones: string|null, archivo: string|null}> $result */
        $result = $cotizaciones->map(function ($cot) use ($minTotal, $minDias) {
            $razonSocial = $cot->proveedor?->persona?->personaJuridica?->razon_social;
            $primerNombre = $cot->proveedor?->persona?->primer_nombre;
            $primerApellido = $cot->proveedor?->persona?->personaNatural?->primer_apellido;

            return [
                'id' => $cot->id,
                'proveedor' => $razonSocial ?? "{$primerNombre} {$primerApellido}",
                'empresa' => $razonSocial ?? '—',
                'total' => $cot->total,
                'dias_entrega' => $cot->dias_entrega,
                'condicion_pago' => $cot->condicionPago->nombre ?? 'N/A',
                'es_ganadora' => $cot->es_elegida,
                'es_mas_barato' => $cot->total == $minTotal,
                'es_mas_rapido' => $cot->dias_entrega == $minDias,
                'es_recomendada' => $cot->id === $this->recomendadaId,
                'observaciones' => $cot->observaciones,
                'archivo' => $cot->archivo_pdf,
            ];
        })->toArray();

        return $result;
    }

    protected function getHeaderActions(): array
    {

        return [
            Action::make('analizar')
                ->label('Analizar Mejor Opción')
                ->icon(Heroicon::CheckBadge)
                ->color('warning')
                ->action('analizarMejorOpcion'),

            Action::make('regresar')
                ->label('Volver a Solicitud')
                ->color('gray')
                ->url($this->getResource()::getUrl('view', ['record' => $this->record])),
        ];
    }
}
