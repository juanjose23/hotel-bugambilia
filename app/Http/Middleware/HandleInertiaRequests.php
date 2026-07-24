<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'exito' => fn () => $request->session()->get('exito'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'hotel' => [
                'name' => config('hotel.name'),
                'slogan' => config('hotel.slogan'),
                'telefono' => config('hotel.telefono'),
                'whatsapp' => config('hotel.whatsapp'),
                'email' => config('hotel.email'),
                'email_reservaciones' => config('hotel.email_reservaciones'),
                'direccion' => config('hotel.direccion'),
                'direccion_corta' => config('hotel.direccion_corta'),
                'checkin' => config('hotel.checkin'),
                'checkout' => config('hotel.checkout'),
                'fundado' => config('hotel.fundado'),
                'logo' => config('hotel.logo'),
                'icon' => config('hotel.icon'),
            ],
        ];
    }
}
