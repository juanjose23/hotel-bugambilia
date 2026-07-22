<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class RequerirAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        if ($user !== null && ! $user->is_admin) {
            Auth::logout();

            return redirect('/')->with('warning', 'Acceso restringido al área administrativa.');
        }

        return $next($request);
    }
}
