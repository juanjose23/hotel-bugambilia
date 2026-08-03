<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Actions\Restaurante\Voucher\GenerarVoucherPagoHTML;
use App\Actions\Restaurante\Voucher\GenerarVoucherPagoPDF;
use App\Actions\Restaurante\Voucher\GenerarVoucherPedidoHTML;
use App\Actions\Restaurante\Voucher\GenerarVoucherPedidoPDF;
use App\Http\Controllers\Controller;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VoucherRestauranteController extends Controller
{
    public function __construct(
        private readonly GenerarVoucherPedidoHTML $voucherPedidoHTML,
        private readonly GenerarVoucherPedidoPDF $voucherPedidoPDF,
        private readonly GenerarVoucherPagoHTML $voucherPagoHTML,
        private readonly GenerarVoucherPagoPDF $voucherPagoPDF,
        private readonly RestauranteRepositorioInterface $restauranteRepositorio,
    ) {}

    public function generar(Request $request, Pedido $pedido): Response
    {
        $tipo = $request->query('tipo', 'pedido');
        $formato = $request->query('formato', 'html');

        if ($tipo === 'pago') {
            $cuentaId = $request->query('cuenta_id');

            if ($cuentaId === null) {
                $cuentaId = $pedido->cuenta_id;
            }

            if ($cuentaId === null) {
                abort(404, 'No se encontró una cuenta de pago para este pedido.');
            }

            $cuenta = $this->restauranteRepositorio->obtenerCuentaPorId((int) $cuentaId);

            if ($cuenta === null) {
                abort(404, 'Cuenta no encontrada.');
            }

            if ($formato === 'pdf') {
                return $this->voucherPagoPDF->ejecutar($cuenta);
            }

            $html = $this->voucherPagoHTML->ejecutar($cuenta);

            return response($html)->header('Content-Type', 'text/html');
        }

        if ($formato === 'pdf') {
            return $this->voucherPedidoPDF->ejecutar($pedido);
        }

        $html = $this->voucherPedidoHTML->ejecutar($pedido);

        return response($html)->header('Content-Type', 'text/html');
    }
}
