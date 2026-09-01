<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CuentaClienteController extends Controller
{
    public function show(Request $request): RedirectResponse
    {
        return redirect()->route('mis-reservas');
    }
}
