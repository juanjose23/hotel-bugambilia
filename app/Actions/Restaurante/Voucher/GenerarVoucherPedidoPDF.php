<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Voucher;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Support\HotelInfo;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GenerarVoucherPedidoPDF
{
    public function ejecutar(Pedido $pedido): StreamedResponse
    {
        $pedido->loadMissing(['items.plato', 'mesa', 'mesero.persona', 'cliente.personaNatural', 'cliente.personaJuridica', 'cuenta.estancia.habitacion', 'cuenta.moneda']);

        $items = $pedido->items->filter(fn ($item) => $item->estado !== EstadoItemPedido::ANULADO);

        $datosHotel = HotelInfo::getBaseData();

        $clienteNombre = $pedido->cliente->nombre_completo ?? ('Cliente '.($pedido->mesa->nombre ?? 'Mostrador'));
        $habitacionNumero = $pedido->cuenta->estancia->habitacion->numero ?? null;

        $simboloMoneda = (string) ($pedido->cuenta->moneda->simbolo ?? 'C$');

        $pdf = Pdf::loadView('reports.restaurante.voucher-pedido', [
            'pedido' => $pedido,
            'items' => $items,
            'clienteNombre' => $clienteNombre,
            'habitacionNumero' => $habitacionNumero,
            'simboloMoneda' => $simboloMoneda,
            'datosHotel' => $datosHotel,
            'fechaEmision' => now()->format('d/m/Y H:i'),
            'codigoReporte' => 'HTB-RES-002',
            'nombreReporte' => 'Comanda Restaurante',
        ])->setPaper('letter', 'portrait');

        $nombreArchivo = "comanda-{$pedido->codigo}.pdf";

        return response()->stream(
            fn () => print ($pdf->output()),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$nombreArchivo}\"",
            ]
        );
    }
}
