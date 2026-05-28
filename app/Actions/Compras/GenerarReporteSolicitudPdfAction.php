<?php

declare(strict_types=1);

namespace App\Actions\Compras;

use App\Models\Compras\Solicitud;
use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarReporteSolicitudPdfAction
{
    public function ejecutar(Solicitud $solicitud): \Barryvdh\DomPDF\PDF
    {
        $solicitud->loadMissing(['colaborador.persona', 'departamentoSolicitante', 'items.producto', 'items.productoVariante']);

        $items = $solicitud->items;

        $logoPath = public_path('img/logo-horizontal.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($logoPath));
        }

        $codigoReporte = 'HTB-COM-001';
        $nombreReporte = 'Solicitud de Compra';

        $paginas = collect([$items->values()]);

        $pdf = Pdf::loadView('reports.compras.solicitud-compra', [
            'solicitud' => $solicitud,
            'paginas' => $paginas,
            'estadoLabel' => $solicitud->estado->label(),
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user()->name ?? 'Sistema',
            'logo_base64' => $logoBase64,
        ]);

        $auditoria = new RegistrarAuditoriaReporteUseCase;
        $auditoria->ejecutar($codigoReporte, [
            'solicitud_id' => $solicitud->id,
            'codigo' => $solicitud->codigo,
        ]);

        return $pdf;
    }
}
