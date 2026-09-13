<?php

declare(strict_types=1);

namespace App\Actions\Restaurante;

final class ConstruirUrlWhatsapp
{
    /**
     * @param  list<string>  $lineas
     */
    public function ejecutar(array $lineas): string
    {
        $configWhatsapp = config('hotel.whatsapp');
        $rawWhatsapp = is_string($configWhatsapp) ? $configWhatsapp : '+50588888888';
        $numWhatsapp = preg_replace('/\D/', '', $rawWhatsapp) ?? '50588888888';

        return "https://wa.me/{$numWhatsapp}?text=".urlencode(implode("\n", $lineas));
    }
}
