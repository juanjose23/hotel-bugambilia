<?php

declare(strict_types=1);

namespace App\Support;

final class HotelInfo
{
    private static ?string $logoCache = null;

    private static ?string $iconCache = null;

    public static function getLogoBase64(): string
    {
        return self::$logoCache ??= self::imageToBase64('images/logo-dark.png');
    }

    public static function getIconBase64(): string
    {
        $icon = config('hotel.icon', 'img/hotel-icon.png');

        return self::$iconCache ??= self::imageToBase64(is_string($icon) ? $icon : null);
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
        $info = self::getInfo();

        $data = [
            'logo_base64' => self::getLogoBase64(),
            'icon_base64' => self::getIconBase64(),
            'hotelInfo' => $info,
            'nombre' => $info['nombre'],
            'telefono' => $info['telefono'],
            'email' => $info['email'],
            'direccion' => $info['direccion'],
            'generadoEn' => now()->format('d/m/Y H:i'),
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user() !== null ? auth()->user()->name : 'Sistema',
        ];

        // Include self-referencing key so views can use $datosHotel directly
        // (used by paginated-table partial and header partials)
        $data['datosHotel'] = $data;

        return $data;
    }
}
