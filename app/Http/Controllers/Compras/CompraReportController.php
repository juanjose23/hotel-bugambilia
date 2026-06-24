<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use Carbon\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

class CompraReportController extends Controller
{
    public function imprimirSolicitud(Solicitud $solicitud): PdfBuilder
    {
        $this->authorize('Compras:ImprimirSolicitud');
        $this->registrarAuditoria('HTB-COM-001', $solicitud);

        return Pdf::view('reports.compras.solicitud', $this->getReportData($solicitud))
            ->name("SOL-{$solicitud->codigo}.pdf")
            ->download();
    }

    public function imprimirOrdenCompra(OrdenCompra $orden): PdfBuilder
    {
        $this->authorize('Compras:ImprimirOrdenCompra');
        $this->registrarAuditoria('HTB-COM-003', $orden);

        return Pdf::view('reports.compras.orden-compra', $this->getReportData($orden))
            ->name("OC-{$orden->codigo}.pdf")
            ->download();
    }

    public function imprimirRecepcion(RecepcionCompra $recepcion): PdfBuilder
    {
        $this->authorize('Compras:ImprimirRecepcion');
        $this->registrarAuditoria('HTB-COM-004', $recepcion);

        return Pdf::view('reports.compras.recepcion', $this->getReportData($recepcion))
            ->name("REC-{$recepcion->codigo}.pdf")
            ->download();
    }

    public function imprimirCotizacion(Cotizacion $cotizacion): PdfBuilder
    {
        $this->authorize('Compras:ImprimirCotizacion');
        $this->registrarAuditoria('HTB-COM-002', $cotizacion);

        return Pdf::view('reports.compras.cotizacion', $this->getReportData($cotizacion))
            ->name("COT-{$cotizacion->id}.pdf")
            ->download();
    }

    public function imprimirComparativa(Solicitud $solicitud): PdfBuilder
    {
        $this->authorize('Compras:ImprimirComparativa');
        $this->registrarAuditoria('HTB-COM-006', $solicitud);

        $solicitud->load([
            'items.producto',
            'items.variante',
            'cotizaciones.proveedor.persona.personaJuridica',
            'cotizaciones.items.variante',
            'cotizaciones.moneda',
        ]);

        return Pdf::view('reports.compras.comparativa', $this->getReportData($solicitud))
            ->name("COMP-{$solicitud->codigo}.pdf")
            ->download();
    }

    public function imprimirResumenDepartamentos(): PdfBuilder
    {
        $this->authorize('Compras:ImprimirReportesCompras');
        try {
            $reqInicio = request('fecha_inicio');
            $fechaInicio = null;
            if (is_string($reqInicio)) {
                $parsed = Carbon::createFromFormat('Y-m-d', $reqInicio);
                if ($parsed instanceof Carbon) {
                    $fechaInicio = $parsed->startOfDay();
                }
            }
            if (! $fechaInicio) {
                $fechaInicio = now()->startOfMonth();
            }

            $reqFin = request('fecha_fin');
            $fechaFin = null;
            if (is_string($reqFin)) {
                $parsed = Carbon::createFromFormat('Y-m-d', $reqFin);
                if ($parsed instanceof Carbon) {
                    $fechaFin = $parsed->endOfDay();
                }
            }
            if (! $fechaFin) {
                $fechaFin = now();
            }

            if ($fechaInicio->gt($fechaFin)) {
                $temp = $fechaInicio;
                $fechaInicio = $fechaFin->copy()->startOfDay();
                $fechaFin = $temp->copy()->endOfDay();
            }
        } catch (\Exception $e) {
            $fechaInicio = now()->startOfMonth();
            $fechaFin = now();
        }

        $data = \DB::table('ordenes_compra as oc')
            ->join('solicitudes_compra as s', 'oc.solicitud_id', '=', 's.id')
            ->join('catalogos as c', 's.departamento_solicitante_id', '=', 'c.id')
            ->select(
                'c.nombre as departamento',
                \DB::raw('count(oc.id) as conteo_ordenes'),
                \DB::raw('sum(oc.total) as total_oc')
            )
            ->whereNull('oc.deleted_at')
            ->whereBetween('oc.fecha_orden', [$fechaInicio, $fechaFin])
            ->groupBy('c.id', 'c.nombre')
            ->get();

        $this->registrarAuditoria('HTB-COM-005', (object) ['id' => 0, 'codigo' => 'GENERAL']);

        return Pdf::view('reports.compras.resumen-departamentos', array_merge($this->getReportData(null), [
            'data' => $data,
            'fechaInicio' => $fechaInicio->format('d/m/Y'),
            'fechaFin' => $fechaFin->format('d/m/Y'),
        ]))
            ->name('Resumen-Compras-Departamentos.pdf')
            ->download();
    }

    protected function registrarAuditoria(string $codigo, mixed $record): void
    {
        if (! is_object($record)) {
            return;
        }

        $idVal = method_exists($record, 'getKey') ? $record->getKey() : ($record->id ?? 0);
        $id = is_numeric($idVal) ? (int) $idVal : 0;

        $codigoVal = $record->codigo ?? $id;
        $codigoRef = is_scalar($codigoVal) ? (string) $codigoVal : (string) $id;

        app(RegistrarAuditoriaReporteUseCase::class)->ejecutar($codigo, [
            'id' => $id,
            'codigo_referencia' => $codigoRef,
        ]);
    }

    /** @return array<string, mixed> */
    protected function getReportData(mixed $record): array
    {
        // Asegurar que las relaciones necesarias estén cargadas para evitar N+1 y datos faltantes
        if (is_object($record) && method_exists($record, 'load')) {
            // Relaciones comunes
            $record->load(['items.producto', 'items.variante']);

            if ($record instanceof Solicitud) {
                $record->load(['colaborador.persona', 'departamentoSolicitante', 'items.unidadMedida']);
            }

            if ($record instanceof OrdenCompra) {
                $record->load(['proveedor.persona', 'condicionPago', 'items.unidadMedida', 'cotizacion.moneda']);
            }

            if ($record instanceof RecepcionCompra) {
                $record->load(['ordenCompra.proveedor.persona', 'ordenCompra.cotizacion.moneda', 'receptor', 'items.unidadMedida']);
            }

            if ($record instanceof Cotizacion) {
                $record->load(['proveedor.persona', 'solicitud.items.unidadMedida', 'moneda']);
            }
        }

        $logoPath = public_path('img/logo-horizontal.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode((string) file_get_contents($logoPath));
        }

        return [
            'record' => $record,
            'logo_base64' => $logoBase64,
            'hotelInfo' => [
                'telefono' => '+505 8713 6805',
                'email' => 'recepcion@bugambiliashotel.com',
                'direccion' => 'Salida Sur Estelí, Restaurante Absoluto 1c. Oeste, 2c. Sur, 1c. Oeste',
            ],
        ];
    }
}
