<?php

declare(strict_types=1);

namespace App\Actions\Facturacion\Impresion;

use App\Repository\Models\Facturacion\Factura;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\MonedaHelper;
use App\Support\NumeroALetras;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class GenerarFacturaPdfAction
{
    use GuardaReporte;

    public function __construct(
        private BarcodeGenerator $barcodeGenerator,
    ) {}

    public function ejecutar(Factura $factura, bool $download = false): StreamedResponse
    {
        $factura->loadMissing([
            'detalles',
            'serie',
            'autorizacionDgi',
            'cliente.persona.personaNatural',
            'cliente.persona.personaJuridica',
            'moneda',
            'venta',
            'cuenta',
            'emisor',
        ]);

        $datosHotel = HotelInfo::getBaseData();
        $simboloMoneda = MonedaHelper::simbolo($factura->moneda);
        $nombreMoneda = $factura->moneda?->codigo === 'USD' ? 'DÓLARES' : 'CÓRDOBAS';
        $totalEnLetras = NumeroALetras::convertir((float) $factura->total, $nombreMoneda);

        $datosReceptor = $factura->datos_receptor ?? [];
        $tieneRuc = isset($datosReceptor['ruc']) && $datosReceptor['ruc'] !== '';
        $tieneRazonSocial = isset($datosReceptor['razon_social']) && $datosReceptor['razon_social'] !== '';

        if (! $tieneRuc && ! $tieneRazonSocial && $factura->cliente?->persona !== null) {
            $persona = $factura->cliente->persona;
            $datosReceptor['ruc'] = $persona->personaJuridica !== null
                ? $persona->personaJuridica->numero_identificacion
                : $persona->personaNatural?->numero_identificacion;
            $datosReceptor['razon_social'] = $persona->personaJuridica !== null
                ? $persona->personaJuridica->razon_social
                : $persona->nombre_completo;
        }

        $codigoReporte = 'HTB-FAC-001';
        $nombreReporte = 'Factura Fiscal Oficial';

        $barcodeBase64 = $this->barcodeGenerator->base64(
            code: (string) ($factura->numero ?: 'FAC-'.$factura->id),
            height: 70,
            widthFactor: 2,
        );

        $layout = new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $pdf = Pdf::loadView('reports.facturacion.factura-fiscal', [
            'factura' => $factura,
            'datosHotel' => $datosHotel,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'simboloMoneda' => $simboloMoneda,
            'totalEnLetras' => $totalEnLetras,
            'datosReceptor' => $datosReceptor,
            'barcodeBase64' => $barcodeBase64,
            'fechaEmision' => $factura->fecha_emision?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
        ])->setPaper('letter', 'portrait');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'factura_id' => $factura->id,
                'numero' => $factura->numero,
            ],
            pdf: $pdf,
        );

        $nombreArchivo = "factura-fiscal-{$factura->numero}.pdf";
        $disposition = $download ? 'attachment' : 'inline';

        return response()->stream(
            static fn () => print ($pdf->output()),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "{$disposition}; filename=\"{$nombreArchivo}\"",
            ]
        );
    }
}
