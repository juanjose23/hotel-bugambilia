<?php

declare(strict_types=1);

namespace App\Support;

final class HotelInfo
{
    public static function getLogoBase64(): string
    {
        return self::imageToBase64('images/logo-dark.png');
    }

    public static function getIconBase64(): string
    {
        $icon = config('hotel.icon', 'img/hotel-icon.png');

        return self::imageToBase64(is_string($icon) ? $icon : null);
    }

    private static function imageToBase64(?string $path): string
    {
        if (! $path || trim($path) === '') {
            return '';
        }

        $fullPath = public_path(trim($path));

        if (! is_file($fullPath)) {
            return '';
        }

        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        $content = file_get_contents($fullPath);
        $base64 = is_string($content) ? base64_encode($content) : '';

        return 'data:image/'.$type.';base64,'.$base64;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getInfo(): array
    {
        return [
            'nombre' => config('hotel.name', 'Hotel Bugambilias'),
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
        $base = [
            'logo_base64' => self::getLogoBase64(),
            'icon_base64' => self::getIconBase64(),
            'hotelInfo' => self::getInfo(),
            'generadoEn' => now()->format('d/m/Y H:i'),
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user()->name ?? 'Sistema',
        ];

        $base['datosHotel'] = $base;

        return $base;
    }
}
