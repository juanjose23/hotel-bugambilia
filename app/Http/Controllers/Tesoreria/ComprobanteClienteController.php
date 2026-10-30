<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tesoreria\PresentarComprobanteRequest;
use App\Interactors\Tesoreria\GestionarComprobantes;
use App\Repository\Models\User;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use App\Repository\Queries\Tesoreria\TesoreriaQuery;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

final class ComprobanteClienteController extends Controller
{
    public function __construct(private TesoreriaRepositorio $repo, private TesoreriaQuery $query, private GestionarComprobantes $comprobantes) {}

    public function index(Request $request, int $cuenta): Response
    {
        abort_unless(config('tesoreria.habilitado') === true, 404);
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $c = $this->repo->cuentaPropia($cuenta, $user);

        return Inertia::render('portal/Comprobantes', ['cuenta' => ['id' => $c->id, 'numero' => $c->numero_cuenta, 'saldo' => (float) $c->saldo, 'moneda_id' => $c->moneda_id, 'moneda' => $c->moneda->codigo ?? ''], 'bancos' => $this->query->bancosCliente(), 'comprobantes' => $this->query->comprobantesCliente($cuenta, $user->id)]);
    }

    public function store(PresentarComprobanteRequest $request, int $cuenta): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $archivo = $request->file('archivo');
        abort_unless($archivo instanceof UploadedFile, 422);
        try {
            $this->comprobantes->presentar($user->id, $cuenta, $request->integer('banco_id'), $request->float('importe'), $request->string('fecha')->toString(), $request->string('referencia')->toString(), $archivo, $request->string('clave')->toString(), $request->filled('aclaracion_id') ? $request->integer('aclaracion_id') : null);
        } catch (DomainException $e) {
            return back()->withErrors(['archivo' => $e->getMessage()]);
        }

        return back()->with('success', 'Comprobante recibido. El dinero queda pendiente hasta la revisión de caja.');
    }
}
