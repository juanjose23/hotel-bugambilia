<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Voucher;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\Pedido;

final class GenerarVoucherPedidoHTML
{
    public function ejecutar(Pedido $pedido): string
    {
        $pedido->loadMissing(['items.plato', 'mesa', 'mesero.persona', 'cliente.personaNatural', 'cliente.personaJuridica', 'cuenta.estancia.habitacion', 'cuenta.moneda']);

        $items = $pedido->items->filter(fn ($item) => $item->estado !== EstadoItemPedido::ANULADO);

        $total = (float) $pedido->total;

        $clienteNombre = $pedido->cliente->nombre_completo ?? ('Cliente '.($pedido->mesa->nombre ?? 'Mostrador'));
        $meseroNombre = $pedido->mesero->persona->nombre_completo ?? null;
        $habitacionNumero = $pedido->cuenta->estancia->habitacion->numero ?? null;

        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Comanda #'.htmlspecialchars($pedido->codigo).'</title>';
        $html .= '<style>';
        $html .= '@page{margin:0;size:80mm auto;}body{font-family:"Courier New",Courier,monospace;font-size:12px;color:#000;background:#fff;margin:0;padding:10px 8px;width:80mm;box-sizing:border-box;line-height:1.3;}';
        $html .= '.header{text-align:center;border-bottom:2px solid #000;padding-bottom:6px;margin-bottom:8px;}';
        $html .= '.header h1{font-size:15px;font-weight:bold;margin:0 0 2px 0;letter-spacing:1px;text-transform:uppercase;}';
        $html .= '.header h2{font-size:13px;margin:2px 0;font-weight:bold;letter-spacing:0.5px;text-transform:uppercase;}';
        $html .= '.badge{display:inline-block;border:1px solid #000;padding:2px 6px;font-weight:bold;font-size:12px;margin-top:3px;text-transform:uppercase;}';
        $html .= '.meta-table{width:100%;margin-bottom:8px;border-bottom:1px dashed #000;padding-bottom:6px;font-size:12px;}';
        $html .= '.meta-table td{padding:1.5px 0;vertical-align:top;}.meta-label{font-weight:bold;width:38%;}';
        $html .= '.items-table{width:100%;border-collapse:collapse;margin-bottom:8px;}';
        $html .= '.items-table th{border-bottom:1px solid #000;text-align:left;padding-bottom:3px;font-weight:bold;text-transform:uppercase;font-size:11px;}';
        $html .= '.items-table td{padding:5px 0;border-bottom:1px dotted #aaa;vertical-align:top;}';
        $html .= '.qty{font-weight:bold;font-size:14px;width:32px;text-align:center;}';
        $html .= '.item-name{font-weight:bold;font-size:13px;}';
        $html .= '.item-note{font-size:11px;font-weight:bold;margin-top:2px;color:#111;}';
        $html .= '.total-section{border-top:2px solid #000;padding-top:6px;margin-top:8px;text-align:right;}';
        $html .= '.total-row{font-size:14px;font-weight:bold;}';
        $html .= '.footer{text-align:center;margin-top:12px;font-size:10px;border-top:1px dashed #000;padding-top:6px;}';
        $html .= '.no-print{margin-bottom:12px;text-align:center;}';
        $html .= '.btn-print{background:#0f172a;color:#fff;border:1px solid #334155;padding:8px 18px;border-radius:6px;font-family:system-ui,sans-serif;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;}';
        $html .= '@media print{.no-print{display:none;}body{padding:0;width:100%;}}</style></head><body>';

        $html .= '<div class="no-print"><button onclick="window.print()" class="btn-print">Imprimir Comanda POS</button></div>';

        $html .= '<div class="header"><h1>HOTEL BUGAMBILIAS</h1><h2>COMANDA RESTAURANTE</h2>';
        $html .= '<div class="badge">Pedido #'.htmlspecialchars($pedido->codigo).'</div></div>';

        $html .= '<table class="meta-table">';
        $html .= '<tr><td class="meta-label">PEDIDO:</td><td><strong>'.htmlspecialchars($pedido->codigo).'</strong></td></tr>';
        $html .= '<tr><td class="meta-label">MESA / UBIC.:</td><td><strong>'.htmlspecialchars($pedido->mesa->nombre ?? 'Llevar / Habitación').'</strong></td></tr>';
        $html .= '<tr><td class="meta-label">FECHA HORA:</td><td>'.$pedido->created_at?->format('d/m/Y H:i:s').'</td></tr>';
        $html .= '<tr><td class="meta-label">CLIENTE:</td><td>'.htmlspecialchars($clienteNombre).'</td></tr>';
        if ($meseroNombre !== null) {
            $html .= '<tr><td class="meta-label">MESERO:</td><td>'.htmlspecialchars($meseroNombre).'</td></tr>';
        }
        if ($habitacionNumero !== null) {
            $html .= '<tr><td class="meta-label">HABITACIÓN:</td><td>'.htmlspecialchars((string) $habitacionNumero).'</td></tr>';
        }
        $html .= '</table>';

        $html .= '<table class="items-table"><thead><tr><th style="width:35px;text-align:center;">CANT</th><th>DESCRIPCIÓN / DETALLE</th></tr></thead><tbody>';
        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td class="qty">x'.(int) $item->cantidad.'</td>';
            $html .= '<td><div class="item-name">'.htmlspecialchars($item->plato->nombre ?? 'Platillo').'</div>';
            if ($item->observaciones) {
                $html .= '<div class="item-note">-> OBS: '.htmlspecialchars($item->observaciones).'</div>';
            } elseif ($item->notas) {
                $html .= '<div class="item-note">-> NOTA: '.htmlspecialchars($item->notas).'</div>';
            }
            $html .= '</td></tr>';
        }
        $html .= '</tbody></table>';

        if ($pedido->notas) {
            $html .= '<div style="border:1px solid #000;padding:5px;margin-top:8px;font-size:11px;">';
            $html .= '<strong>OBSERVACIONES GENERALES:</strong><br>'.htmlspecialchars($pedido->notas);
            $html .= '</div>';
        }

        $html .= '<div class="total-section">';
        $html .= '<div class="total-row">TOTAL PEDIDO: '.htmlspecialchars((string) ($pedido->cuenta->moneda->simbolo ?? 'C$')).' '.number_format($total, 2).'</div>';
        $html .= '</div>';

        $html .= '<div class="footer"><p>HOTEL BUGAMBILIAS POS</p><p>Impresión Automática por Área</p></div>';
        $html .= '<script>window.addEventListener("DOMContentLoaded",()=>{setTimeout(()=>{window.print()},300)});</script>';
        $html .= '</body></html>';

        return $html;
    }
}
