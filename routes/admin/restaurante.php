<?php

declare(strict_types=1);

use App\Http\Controllers\Restaurante\ComandaController;
use App\Http\Controllers\Restaurante\VoucherRestauranteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Comandas & Vouchers de Restaurante (Admin)
|--------------------------------------------------------------------------
*/
Route::get('/restaurante/pedidos/{pedido}/comanda', [ComandaController::class, 'imprimir'])
    ->name('restaurante.comanda');

Route::get('/restaurante/pedidos/{pedido}/voucher', [VoucherRestauranteController::class, 'generar'])
    ->name('restaurante.voucher');
