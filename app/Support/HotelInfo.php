<?php

declare(strict_types=1);

namespace App\Support;

final class HotelInfo
{
    public static function getLogoBase64(): string
    {
        $logoPath = public_path(config('hotel.logo'));

        if (! file_exists($logoPath)) {
            return '';
        }

        $type = pathinfo($logoPath, PATHINFO_EXTENSION);

        return 'data:image/'.$type.';base64,'.base64_encode((string) file_get_contents($logoPath));
    }

    /**
     * @return array<string, mixed>
     */
    public static function getInfo(): array
    {
        return [
            'telefono' => config('hotel.telefono'),
            'email' => config('hotel.email'),
            'direccion' => config('hotel.direccion'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function getBaseData(): array
    {
        return [
            'logo_base64' => self::getLogoBase64(),
            'hotelInfo' => self::getInfo(),
            'generadoEn' => now()->format('d/m/Y H:i'),
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user()->name ?? 'Sistema',
        ];
    }
}
