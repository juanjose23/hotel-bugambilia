<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Voucher;

use App\Repository\Models\Cuentas\Cuenta;

final class GenerarVoucherPagoHTML
{
    public function ejecutar(Cuenta $cuenta): string
    {
        $cuenta->loadMissing(['pagos', 'cliente', 'detalles']);

        $pago = $cuenta->pagos->last();
        $clienteNombre = $cuenta->cliente->nombre_completo ?? 'Cliente General';
        $metodoPago = $pago?->forma_pago->getLabel() ?? 'N/D';
        $montoPagado = $pago !== null ? (float) $pago->monto : 0.0;
        $totalCuenta = (float) $cuenta->total;
        $vuelto = max(0.0, $montoPagado - $totalCuenta);

        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Voucher Pago '.htmlspecialchars($cuenta->numero_cuenta).'</title>';
        $html .= '<style>';
        $html .= '@page{margin:0;size:80mm auto;}body{font-family:"Courier New",Courier,monospace;font-size:12px;color:#000;background:#fff;margin:0;padding:10px 8px;width:80mm;box-sizing:border-box;line-height:1.3;}';
        $html .= '.header{text-align:center;border-bottom:2px solid #000;padding-bottom:6px;margin-bottom:8px;}';
        $html .= '.header h1{font-size:15px;font-weight:bold;margin:0 0 2px 0;letter-spacing:1px;text-transform:uppercase;}';
        $html .= '.header h2{font-size:13px;margin:2px 0;font-weight:bold;}';
        $html .= '.badge{display:inline-block;border:1px solid #000;padding:2px 6px;font-weight:bold;font-size:12px;margin-top:3px;text-transform:uppercase;background:#d1fae5;color:#065f46;}';
        $html .= '.meta-table{width:100%;margin-bottom:8px;border-bottom:1px dashed #000;padding-bottom:6px;font-size:12px;}';
        $html .= '.meta-table td{padding:1.5px 0;vertical-align:top;}.meta-label{font-weight:bold;width:38%;}';
        $html .= '.total-section{border-top:2px solid #000;padding-top:6px;margin-top:8px;text-align:right;}';
        $html .= '.total-row{font-size:14px;font-weight:bold;}';
        $html .= '.vuelto{font-size:13px;font-weight:bold;color:#065f46;margin-top:4px;}';
        $html .= '.footer{text-align:center;margin-top:12px;font-size:10px;border-top:1px dashed #000;padding-top:6px;}';
        $html .= '.no-print{margin-bottom:12px;text-align:center;}';
        $html .= '.btn-print{background:#0f172a;color:#fff;border:1px solid #334155;padding:8px 18px;border-radius:6px;font-family:system-ui,sans-serif;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;}';
        $html .= '@media print{.no-print{display:none;}body{padding:0;width:100%;}}</style></head><body>';

        $html .= '<div class="no-print"><button onclick="window.print()" class="btn-print">Imprimir Voucher de Pago</button></div>';

        $html .= '<div class="header"><h1>HOTEL BUGAMBILIAS</h1><h2>COMPROBANTE DE PAGO</h2>';
        $html .= '<div class="badge">PAGO CONFIRMADO</div></div>';

        $html .= '<table class="meta-table">';
        $html .= '<tr><td class="meta-label">CUENTA:</td><td><strong>'.htmlspecialchars($cuenta->numero_cuenta).'</strong></td></tr>';
        $html .= '<tr><td class="meta-label">FECHA:</td><td>'.$cuenta->cerrada_at?->format('d/m/Y H:i').'</td></tr>';
        $html .= '<tr><td class="meta-label">CLIENTE:</td><td>'.htmlspecialchars($clienteNombre).'</td></tr>';
        $html .= '<tr><td class="meta-label">MÉTODO:</td><td>'.htmlspecialchars($metodoPago).'</td></tr>';
        if ($pago?->referencia_transaccion) {
            $html .= '<tr><td class="meta-label">REFERENCIA:</td><td>'.htmlspecialchars($pago->referencia_transaccion).'</td></tr>';
        }
        $html .= '</table>';

        $simboloMoneda = (string) ($cuenta->moneda->simbolo ?? 'C$');

        $html .= '<div class="total-section">';
        $html .= '<div style="font-size:11px;">Total a Pagar: '.htmlspecialchars($simboloMoneda).' '.number_format($totalCuenta, 2).'</div>';
        $html .= '<div style="font-size:11px;">Monto Recibido: '.htmlspecialchars($simboloMoneda).' '.number_format($montoPagado, 2).'</div>';
        if ($vuelto > 0) {
            $html .= '<div class="vuelto">VUELTO: '.htmlspecialchars($simboloMoneda).' '.number_format($vuelto, 2).'</div>';
        }
        $html .= '<div class="total-row" style="margin-top:6px;">PAGO CONFIRMADO</div>';
        $html .= '</div>';

        $html .= '<div class="footer"><p>HOTEL BUGAMBILIAS — Comprobante de Pago</p><p>'.now()->format('d/m/Y H:i:s').'</p></div>';
        $html .= '<script>window.addEventListener("DOMContentLoaded",()=>{setTimeout(()=>{window.print()},300)});</script>';
        $html .= '</body></html>';

        return $html;
    }
}
