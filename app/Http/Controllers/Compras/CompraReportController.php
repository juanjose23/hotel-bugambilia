<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\UseCases\Reportes\RegistrarAuditoriaReporteUseCase;
use Carbon\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

class CompraReportController extends Controller
{
    public function imprimirSolicitud(Solicitud $solicitud): PdfBuilder
    {
        $this->registrarAuditoria('HTB-COM-001', $solicitud);

        return Pdf::view('reports.compras.solicitud', $this->getReportData($solicitud))
            ->name("SOL-{$solicitud->codigo}.pdf")
            ->download();
    }

    public function imprimirOrdenCompra(OrdenCompra $orden): PdfBuilder
    {
        $this->registrarAuditoria('HTB-COM-003', $orden);

        return Pdf::view('reports.compras.orden-compra', $this->getReportData($orden))
            ->name("OC-{$orden->codigo}.pdf")
            ->download();
    }

    public function imprimirRecepcion(RecepcionCompra $recepcion): PdfBuilder
    {
        $this->registrarAuditoria('HTB-COM-004', $recepcion);

        return Pdf::view('reports.compras.recepcion', $this->getReportData($recepcion))
            ->name("REC-{$recepcion->codigo}.pdf")
            ->download();
    }

    public function imprimirCotizacion(Cotizacion $cotizacion): PdfBuilder
    {
        $this->registrarAuditoria('HTB-COM-002', $cotizacion);

        return Pdf::view('reports.compras.cotizacion', $this->getReportData($cotizacion))
            ->name("COT-{$cotizacion->id}.pdf")
            ->download();
    }

    public function imprimirResumenDepartamentos(): PdfBuilder
    {
        try {
            $fechaInicio = request('fecha_inicio')
                ? Carbon::createFromFormat('Y-m-d', request('fecha_inicio'))->startOfDay()
                : now()->startOfMonth();
            
            $fechaFin = request('fecha_fin')
                ? Carbon::createFromFormat('Y-m-d', request('fecha_fin'))->endOfDay()
                : now();

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
        app(RegistrarAuditoriaReporteUseCase::class)->ejecutar($codigo, [
            'id' => $record->id,
            'codigo_referencia' => $record->codigo ?? $record->id,
        ]);
    }

    /** @return array<string, mixed> */
    protected function getReportData(mixed $record): array
    {
        // Asegurar que las relaciones necesarias estén cargadas para evitar N+1 y datos faltantes
        if ($record !== null && method_exists($record, 'load')) {
            $record->load(['items.producto', 'items.variante', 'items.unidadMedida']);

            if ($record instanceof Solicitud) {
                $record->load(['colaborador.persona', 'departamentoSolicitante']);
            }

            if ($record instanceof OrdenCompra) {
                $record->load(['proveedor.persona', 'condicionPago']);
            }

            if ($record instanceof RecepcionCompra) {
                $record->load(['ordenCompra.proveedor.persona', 'receptor']);
            }
        }

        $logoPath = public_path('img/logo-horizontal.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($logoPath));
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
