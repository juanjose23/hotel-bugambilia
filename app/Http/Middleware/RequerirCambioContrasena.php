<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class RequerirCambioContrasena
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        if ($user !== null && $user->password_change_required === true) {
            if (! $request->routeIs('cambiar-contrasena') && ! $request->routeIs('logout')) {
                return redirect()->route('cambiar-contrasena');
            }
        }

        return $next($request);
    }
}
