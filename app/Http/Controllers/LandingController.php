<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Interactors\Landing\ObtenerDatosLanding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class LandingController extends Controller
{
    public function __invoke(Request $request, ObtenerDatosLanding $interactor): RedirectResponse|Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user !== null && ! $user->is_admin) {
                return redirect()->route('portal');
            }
        }

        $datos = $interactor->ejecutar();

        return Inertia::render('inicio/Inicio', $datos);
    }
}
