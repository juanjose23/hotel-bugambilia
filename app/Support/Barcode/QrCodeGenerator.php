<?php

declare(strict_types=1);

namespace App\Support\Barcode;

final class QrCodeGenerator
{
    public function base64(string $text, int $size = 150): string
    {
        $url = 'https://quickchart.io/qr?text='.urlencode($text).'&size='.$size.'&margin=1';

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                ],
            ]);

            $png = @file_get_contents($url, false, $context);

            if ($png !== false && $png !== '') {
                return 'data:image/png;base64,'.base64_encode($png);
            }
        } catch (\Throwable) {
            // Fallback
        }

        return '';
    }
}
