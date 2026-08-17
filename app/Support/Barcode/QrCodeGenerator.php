<?php

declare(strict_types=1);

namespace App\Support\Barcode;

use Illuminate\Support\Facades\Http;

final class QrCodeGenerator
{
    public function base64(string $text, int $size = 150): string
    {
        $url = 'https://quickchart.io/qr?text='.urlencode($text).'&size='.$size.'&margin=1';

        try {
            $response = Http::timeout(3)->get($url);

            if ($response->successful() && $response->body() !== '') {
                return 'data:image/png;base64,'.base64_encode($response->body());
            }
        } catch (\Throwable) {
            // Fallback
        }

        return '';
    }
}
