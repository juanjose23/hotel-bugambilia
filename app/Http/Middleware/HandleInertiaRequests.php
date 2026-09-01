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
        $user = $request->user();
        $user?->loadMissing('persona');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user === null ? null : [
                    'id' => $user->id,
                    'name' => $user->persona?->nombre_completo ?: $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'persona' => $user->persona === null ? null : [
                        'id' => $user->persona->id,
                        'nombre_completo' => $user->persona->nombre_completo,
                        'telefono' => $user->persona->telefono,
                    ],
                ],
            ],
            'flash' => [
                'exito' => fn () => $request->session()->get('exito'),
                'success' => fn () => $request->session()->get('success') ?? $request->session()->get('exito'),
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
